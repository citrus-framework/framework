<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus;

use Citrus\Authentication\AuthenticationException;
use Citrus\Authentication\AuthItem;
use Citrus\Authentication\AuthType;
use Citrus\Authentication\Database;
use Citrus\Authentication\Protocol;
use Citrus\Configure\Configurable;
use Citrus\Configure\ConfigureException;
use Citrus\Database\Connection\Connection;
use Citrus\Database\DSN;
use Citrus\Variable\Singleton;

/**
 * 認証処理
 */
class Authentication extends Configurable
{
    use Singleton;

    /** セッション保存キー */
    public const string SESSION_KEY = 'authentication';

    /** @var string 認証テーブル名 */
    public static string $AUTHORIZE_TABLE_NAME = 'users';

    /**
     * @param Protocol|null $protocol 認証タイプインスタンス
     * @throws ConfigureException
     */
    public function __construct(
        public Protocol|null $protocol = null,
    ) {
        // 設定の読み込み
        $this->loadConfigures(Configure::callConfigures());
    }

    /**
     * {@inheritDoc}
     */
    public function loadConfigures(array $configures = []): Configurable
    {
        // 空の場合は返却
        if (0 === count($configures))
        {
            return $this;
        }

        // 設定配列の読み込み
        parent::loadConfigures($configures);

        // 認証プロバイダ
        if (AuthType::DATABASE === AuthType::from($this->configures['type']))
        {
            $connection = new Connection(DSN::getInstance()->loadConfigures($this->configures));
            $this->protocol = new Database($connection);
        }

        return $this;
    }

    /**
     * 認証処理
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
     * @param AuthItem|null $item
     * @return bool true:チェック成功, false:チェック失敗
     * @throws AuthenticationException
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
