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
        #[\SensitiveParameter] public Connection $connection,
        #[\SensitiveParameter] protected JWT|null $jwt = null,
        #[\SensitiveParameter] protected Executor|null $executor = null,
    ) {
        $this->jwt = $jwt ?? new JWT($this->connection);
        $this->executor = $executor ?? new Executor($this->connection);
    }

    /**
     * 認証処理
     * @param AuthItem $item
     * @return bool true:認証成功, false:認証失敗
     */
    public function authorize(#[\SensitiveParameter] AuthItem $item): bool
    {
        // ログインID、パスワード のどちらかが null もしくは 空文字 だった場合は認証失敗
        if (Strings::isEmpty($item->user_id) === true or Strings::isEmpty($item->password) === true)
        {
            return false;
        }

        // 対象ユーザーがいて、パスワードが一致するか？
        $result = (new UserLookup($this->executor))->findVerified($item->user_id, $item->password);

        // いない、またはパスワードが一致しなければ認証失敗
        if ($result === null)
        {
            return false;
        }

        // 認証情報の保存
        $item->token = $this->jwt->encode(['user_id' => $item->user_id]);
        $item->expired_at = date('Y-m-d H:i:s', $this->jwt->callExpiredAt());
        $item->password = null;

        // データベースに現在のトークンと保持期間の保存
        $this->executor->build(
            (new Builder(Authentication::$AUTHORIZE_TABLE_NAME))->updateQuery()
                ->properties($item->nonnullProperties())
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
    public function isAuthenticated(#[\SensitiveParameter] AuthItem|null $item = null): bool
    {
        // 指定されない場合はsessionから取得
        $item ??= Session::$session->call(Authentication::SESSION_KEY);
        // 認証itemが無い
        AuthenticationException::exceptionIf(
            is_null($item),
            '認証情報がない'
        );

        $lookup = new UserLookup($this->executor);

        // トークンが無く、ID・パスワードがある場合はデータ取得
        $item = (new CredentialResolver($lookup))->resolve($item);

        // ユーザーIDとトークン、認証期間があるか、認証期間が切れていないか
        (new ItemVerifier())->verify($item);

        $result = $lookup->find($item->user_id);

        // ユーザーがいない(null は検証済みのトークンと一致しない)、またはトークンがDBの値と違えば認証失敗
        if ($result?->token !== $item->token)
        {
            return false;
        }

        Session::$session->add(Authentication::SESSION_KEY, $item);
        Session::commit();

        return true;
    }

    /**
     * 認証用の接続で、Bearer のトークンを確かめる検証を作る
     * @return TokenVerifier
     */
    public function callTokenVerifier(): TokenVerifier
    {
        return new TokenVerifier($this->jwt, new UserLookup($this->executor));
    }
}
