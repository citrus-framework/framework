<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus;

use Citrus\Authentication\AuthItem;
use Citrus\Authentication\Database;
use Citrus\Authentication\Protocol;
use Citrus\Configure\Configurable;
use Citrus\Database\Connection\Connection;
use Citrus\Database\DSN;
use Citrus\Session\SessionException;
use Citrus\Variable\Dates;
use Citrus\Variable\Singleton;

/**
 * 認証処理
 */
class Authentication extends Configurable
{
    use Singleton;

    /** @var string 認証タイプ(データベース) */
    public const TYPE_DATABASE = 'database';

    /** @var string セッション保存キー */
    public const SESSION_KEY = 'authentication';

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
     * @param AuthItem $item
     * @return bool true:認証成功, false:認証失敗
     */
    public function authorize(AuthItem $item): bool
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
     * @return bool true:処理成功
     */
    public function deAuthorize(): bool
    {
        if (true === is_null($this->protocol))
        {
            return false;
        }

        return $this->protocol->deAuthorize();
    }



    /**
     * 認証のチェック
     * 認証できていれば期間の延長
     *
     * @param AuthItem|null $item
     * @return bool true:チェック成功, false:チェック失敗
     */
    public function isAuthenticated(AuthItem|null $item = null): bool
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
    public static function generateToken(string|null $key = null): string
    {
        // セッションが無効 もしくは 存在しない場合
        SessionException::exceptionIf(
            (PHP_SESSION_ACTIVE !== Session::status()),
            'セッションが無効 もしくは 存在しません。'
        );

        // アルゴリズムチェック
        SessionException::exceptionElse(
            in_array(self::$TOKEN_ALGO, hash_algos()),
            '未定義のtoken生成アルゴリズムです。'
        );

        // tokenキー
        $key = ($key ?? Session::$sessionId);

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
        return Dates::now()->addSecond(self::$KEEP_SECOND)->format('Y-m-d H:i:s');
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
