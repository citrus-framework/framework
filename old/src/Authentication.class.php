<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus;

use Citrus\Authentication\Database;
use Citrus\Authentication\Item;
use Citrus\Authentication\Protocol;
use Citrus\Configure\Configurable;
use Citrus\Database\Connection\Connection;
use Citrus\Database\DSN;
use Citrus\Session\SessionException;
use Citrus\Variable\Singleton;

/**
 * 認証処理
 */
class Authentication extends Configurable
{
    use Singleton;

    /** @var string 認証タイプ(データベース) */
    const TYPE_DATABASE = 'database';

    /** @var string セッション保存キー */
    const SESSION_KEY = 'authentication';

    /** @var string 認証テーブル名 */
    public static $AUTHORIZE_TABLE_NAME = 'users';

    /** @var string token生成アルゴリズム */
    public static $TOKEN_ALGO = 'sha256';

    /** @var int ログイン維持時間(秒) */
    public static $KEEP_SECOND = (60 * 60 * 24);

    /** @var Protocol 認証タイプインスタンス */
    public $protocol = null;



    /**
     * {@inheritDoc}
     */
    public function loadConfigures(array $configures = []): Configurable
    {
        // 設定配列の読み込み
        parent::loadConfigures($configures);

        // 認証プロバイダ
        if (self::TYPE_DATABASE === $this->configures['type'])
        {
            $connection = new Connection(DSN::getInstance()->loadConfigures($this->configures));
            $this->protocol = new Database($connection);
        }

        return $this;
    }



    /**
     * 認証処理
     *
     * @param Item $item
     * @return bool ture:認証成功, false:認証失敗
     */
    public function authorize(Item $item): bool
    {
        if (true === is_null($this->protocol))
        {
            return false;
        }

        return $this->protocol->authorize($item);
    }



    /**
     * 認証解除処理
     *
     * @return bool ture:認証成功, false:認証失敗
     */
    public function deauthorize(): bool
    {
        if (true === is_null($this->protocol))
        {
            return false;
        }

        return $this->protocol->deauthorize();
    }



    /**
     * 認証のチェック
     * 認証できていれば期間の延長
     *
     * @param Item|null $item
     * @return bool true:チェック成功, false:チェック失敗
     */
    public function isAuthenticated(Item $item = null): bool
    {
        if (true === is_null($this->protocol))
        {
            return false;
        }

        return $this->protocol->isAuthenticated($item);
    }



    /**
     * ログイントークンの生成
     *
     * @param string|null $key
     * @return string
     * @throws CitrusException
     * @throws SessionException
     */
    public static function generateToken(string $key = null): string
    {
        // セッションが無効 もしくは 存在しない場合
        if (PHP_SESSION_ACTIVE !== Session::status())
        {
            throw new SessionException('セッションが無効 もしくは 存在しません。');
        }

        // アルゴリズムチェック
        if (false === in_array(self::$TOKEN_ALGO, hash_algos()))
        {
            throw new CitrusException('未定義のtoken生成アルゴリズムです。');
        }

        // tokenキー
        $key = NVL::NVL($key, Session::$sessionId);

        // token生成し返却
        return hash(self::$TOKEN_ALGO, $key);
    }



    /**
     * ログイン維持制限時間の生成
     *
     * @return string
     */
    public static function generateKeepAt(): string
    {
        return date('Y-m-d H:i:s', Citrus::$TIMESTAMP_INT + self::$KEEP_SECOND);
    }



    /**
     * {@inheritDoc}
     */
    protected function configureKey(): string
    {
        return 'authentication';
    }



    /**
     * {@inheritDoc}
     */
    protected function configureDefaults(): array
    {
        return [
            'type' => 'database',
        ];
    }



    /**
     * {@inheritDoc}
     */
    protected function configureRequires(): array
    {
        return [
            'type',
        ];
    }
}
