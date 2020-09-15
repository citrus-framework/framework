<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Authentication;

use Citrus\Authentication;
use Citrus\Database\Connection\Connection;
use Citrus\Logger;
use Citrus\Query\Builder;
use Citrus\Session;
use Citrus\Variable\Strings;

/**
 * このモジュールを利用する場合は以下の構成のテーブルが必要です
 *
CREATE TABLE IF NOT EXISTS users (
    user_id CHARACTER VARYING(32) NOT NULL,
    password CHARACTER VARYING(64) NOT NULL,
    token TEXT,
    expired_at TIMESTAMP WITHOUT TIME ZONE,
    status INTEGER DEFAULT 0 NOT NULL,
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT current_timestamp NOT NULL,
    updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT current_timestamp NOT NULL,
    rowid SERIAL NOT NULL,
    rev INTEGER DEFAULT 1 NOT NULL
);
COMMENT ON COLUMN users.user_id IS 'ユーザーID';
COMMENT ON COLUMN users.paswword IS 'パスワードハッシュ';
COMMENT ON COLUMN users.token IS 'アクセストークン';

ALTER TABLE users ADD CONSTRAINT pk_users PRIMARY KEY (user_id);
CREATE INDEX IF NOT EXISTS idx_users_user_id_token ON users (user_id, token);
 */
class Database extends Protocol
{
    /** @var Connection */
    public $connection;



    /**
     * constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }



    /**
     * 認証処理
     *
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
        $condition = new AuthItem();
        $condition->user_id = $item->user_id;
        /** @var AuthItem $result */
        $result = (new Builder($this->connection))->select($table_name, $condition)->execute(AuthItem::class)->one();
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
        $item->token = Authentication::generateToken();
        $item->expired_at = Authentication::generateKeepAt();
        $item->password = null;

        // データベースに現在のトークンと保持期間の保存
        $condition = new AuthItem();
        $condition->rowid = $result->rowid;
        $condition->rev = $result->rev;
        (new Builder($this->connection))->update($table_name, $item, $condition)->execute();
        Session::$session->add(Authentication::SESSION_KEY, $item);
        Session::commit();

        return true;
    }


    /**
     * 認証解除処理
     *
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
     *
     * @param AuthItem|null $item
     * @return bool true:チェック成功, false:チェック失敗
     */
    public function isAuthenticated(AuthItem $item = null): bool
    {
        // 指定されない場合はsessionから取得
        if (true === is_null($item))
        {
            $item = Session::$session->call(Authentication::SESSION_KEY);
        }
        // 認証itemが無い
        if (true === is_null($item))
        {
            Logger::debug('ログアウト:認証Itemが無い');
            Logger::debug(Session::$session);
            return false;
        }
        // ユーザーIDとトークン、認証期間があるか
        if (true === is_null($item->user_id) or true === is_null($item->token) or true === is_null($item->expired_at))
        {
            Logger::debug('ログアウト:ユーザIDが無い(user_id=%s)、もしくはトークンが無い(token=%s)、もしくはタイムアウト(expired_at=%s)',
                $item->user_id,
                $item->token,
                $item->expired_at
                );
            return false;
        }

        // すでに認証期間が切れている
        $expired_ts = strtotime($item->expired_at);
        $now_ts = time();
        if ($expired_ts < $now_ts)
        {
            Logger::debug('ログアウト:タイムアウト(%s) < 現在時間(%s)',
                $expired_ts,
                $now_ts
            );
            return false;
        }

        // 対象テーブル
        $table_name = Authentication::$AUTHORIZE_TABLE_NAME;

        // まだ認証済みなので、認証期間の延長
        $authentic = new AuthItem();
        $authentic->expired_at = Authentication::generateKeepAt();
        $condition = new AuthItem();
        $condition->user_id = $item->user_id;
        $condition->token = $item->token;
        // 更新
        $result = (new Builder($this->connection))->update($table_name, $authentic, $condition)->execute();

        // 時間を延長
        /** @var AuthItem $item */
        $item = Session::$session->call(Authentication::SESSION_KEY);
        $item->expired_at = $authentic->expired_at;
        Session::$session->add(Authentication::SESSION_KEY, $item);
        Session::commit();

        return ($result > 0);
    }
}
