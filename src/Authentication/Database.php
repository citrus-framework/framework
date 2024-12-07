<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Authentication;

use Citrus\Authentication;
use Citrus\CitrusException;
use Citrus\Database\Connection\Connection;
use Citrus\Query\Builder;
use Citrus\Query\Executor;
use Citrus\Session;
use Citrus\Variable\Strings;

/**
 * このモジュールを利用する場合は以下の構成のテーブルが必要です
 *
 * CREATE TABLE IF NOT EXISTS users (
 *     user_id CHARACTER VARYING(32) NOT NULL,
 *     password CHARACTER VARYING(64) NOT NULL,
 *     token TEXT,
 *     expired_at TIMESTAMP WITHOUT TIME ZONE,
 *     status INTEGER DEFAULT 0 NOT NULL,
 *     created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT current_timestamp NOT NULL,
 *     updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT current_timestamp NOT NULL,
 *     rowid SERIAL NOT NULL,
 *     rev INTEGER DEFAULT 1 NOT NULL
 * );
 * COMMENT ON COLUMN users.user_id IS 'ユーザーID';
 * COMMENT ON COLUMN users.paswword IS 'パスワードハッシュ';
 * COMMENT ON COLUMN users.token IS 'アクセストークン';
 *
 * ALTER TABLE users ADD CONSTRAINT pk_users PRIMARY KEY (user_id);
 * CREATE INDEX IF NOT EXISTS idx_users_user_id_token ON users (user_id, token);
 */
class Database extends Protocol
{
    /**
     * constructor.
     * @param Connection $connection
     * @param JWT|null $jwt
     * @param Executor|null $executor
     */
    public function __construct(
        public Connection $connection,
        protected JWT|null $jwt = null,
        protected Executor|null $executor = null,
    ) {
        $this->jwt = $jwt ?? new JWT($this->connection);
        $this->executor = $executor ?? new Executor($this->connection);
    }

    /**
     * 認証処理
     * @param AuthItem $item
     * @return bool true:認証成功, false:認証失敗
     */
    public function authorize(AuthItem $item): bool
    {
        // ログインID、パスワード のどちらかが null もしくは 空文字 だった場合は認証失敗
        if (true === Strings::isEmpty($item->user_id) or true === Strings::isEmpty($item->password))
        {
            return false;
        }

        // 対象テーブル
        $table_name = Authentication::$AUTHORIZE_TABLE_NAME;

        // 対象ユーザーがいるか？
        /** @var AuthItem $result */
        $result = $this->executor
            ->build(
                (new Builder($table_name))->selectQuery()
                    ->whereEqual('user_id', $item->user_id)
                    ->resultClass(AuthItem::class)
            )
            ->fetch()
            ->one();

        // いなければ認証失敗
        if (true === is_null($result))
        {
            return false;
        }

        // パスワード照合
        if (false === password_verify($item->password, $result->password))
        {
            return false;
        }

        // 認証情報の保存
        $item->token = $this->jwt->encode(['user_id', $item->user_id]);
        $item->expired_at = date('Y-m-d H:i:s', $this->jwt->callExpiredAt());
        $item->password = null;

        // データベースに現在のトークンと保持期間の保存
        $this->executor->build(
            (new Builder($table_name))->updateQuery()
                ->properties($item->properties())
                ->whereEqual('rowid', $result->rowid)
                ->whereEqual('rev', $result->rev)
        )->execute();

        // セッションに保持
        Session::$session->add(Authentication::SESSION_KEY, $item);
        Session::commit();

        return true;
    }

    /**
     * 認証解除処理
     * @return bool true:処理成功
     */
    public function deAuthorize(): bool
    {
        Session::$session->remove(Authentication::SESSION_KEY);
        Session::commit();

        return true;
    }

    /**
     * 認証のチェック
     * 認証できていれば期間の延長
     * @param AuthItem|null $item
     * @return bool true:チェック成功, false:チェック失敗
     * @throws AuthenticationException
     * @throws CitrusException
     */
    public function isAuthenticated(AuthItem|null $item = null): bool
    {
        // 指定されない場合はsessionから取得
        $item ??= Session::$session->call(Authentication::SESSION_KEY);
        // 認証itemが無い
        AuthenticationException::exceptionIf(
            is_null($item),
            '認証情報がない'
        );

        // 対象テーブル
        $table_name = Authentication::$AUTHORIZE_TABLE_NAME;

        // トークンが無く、ID・パスワードがある場合はデータ取得
        if (is_null($item->token) && !is_null($item->user_id) && !is_null($item->password))
        {
            // 対象ユーザーがいるか？
            /** @var AuthItem $result */
            $result = $this->executor
                ->build(
                    (new Builder($table_name))->selectQuery()
                        ->whereEqual('user_id', $item->user_id)
                        ->resultClass(AuthItem::class)
                )
                ->fetch()
                ->one();

            // いなければ認証失敗
            AuthenticationException::exceptionIf(
                is_null($result),
                sprintf(
                    '存在しないユーザーのログイン試行です(%s : %s)',
                    $item->user_id,
                    $item->password,
                ),
            );
            // パスワード照合
            AuthenticationException::exceptionElse(
                password_verify($item->password, $result->password),
                sprintf(
                    'パスワード照合に失敗しました(%s : %s)',
                    $item->user_id,
                    $item->password,
                ),
            );
            $item = $result;
        }

        // ユーザーIDとトークン、認証期間があるか
        AuthenticationException::exceptionIf(
            is_null($item->user_id) or is_null($item->token) or is_null($item->expired_at),
            sprintf(
                'ユーザIDが無い(user_id=%s)、もしくはトークンが無い(token=%s)、もしくはタイムアウト(expired_at=%s)',
                $item->user_id,
                $item->token,
                $item->expired_at
            ),
        );

        // すでに認証期間が切れている
        $expired_ts = strtotime($item->expired_at);
        $now_ts = time();
        AuthenticationException::exceptionIf(
            $expired_ts < $now_ts,
            sprintf(
                'タイムアウト(%s) < 現在時間(%s)',
                $expired_ts,
                $now_ts
            ),
        );

        $result = $this->executor
            ->build(
                (new Builder($table_name))->selectQuery()
                    ->whereEqual('user_id', $item->user_id)
                    ->resultClass(AuthItem::class)
            )
            ->fetch()
            ->one();

//        // まだ認証済みなので、認証期間の延長
//        $authentic = new AuthItem();
//        $authentic->expired_at = date('Y-m-d H:i:s', $this->jwt->callExpiredAt());
//        $condition = new AuthItem();
//        $condition->user_id = $item->user_id;
//        $condition->token = $item->token;
//        // 更新
//        $result = (new Builder($this->connection))->update($table_name, $authentic, $condition)->execute();

        Session::$session->add(Authentication::SESSION_KEY, $item);
        Session::commit();

        return !is_null($result);
    }
}
