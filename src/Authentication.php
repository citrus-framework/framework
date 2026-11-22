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
use Citrus\Authentication\JWTException;
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
        #[\SensitiveParameter] public Protocol|null $protocol = null,
    ) {
        // 設定の読み込み
        $this->loadConfigures(Configure::callConfigures());
    }

    /**
     * {@inheritDoc}
     */
    public function loadConfigures(#[\SensitiveParameter] array $configures = []): Configurable
    {
        // 空の場合は返却
        if (count($configures) === 0)
        {
            return $this;
        }

        // 設定配列の読み込み
        parent::loadConfigures($configures);

        // 認証プロバイダ
        if (AuthType::from($this->configures['type']) === AuthType::DATABASE)
        {
            $connection = new Connection(DSN::getInstance()->loadConfigures($this->configures));
            $this->protocol = new Database($connection);
        }

        return $this;
    }

    /**
     * Bearer のトークンの署名・期限と、DBのユーザーのトークンとの一致を確かめる
     * @param string $token トークン
     * @return void
     * @throws AuthenticationException 認証の設定が無い、ユーザーがいない、またはDBのトークンと違う
     * @throws JWTException            トークンが空、または署名・期限が有効ではない
     */
    public function verifyToken(#[\SensitiveParameter] string $token): void
    {
        AuthenticationException::exceptionElse($this->protocol instanceof Database, '認証の設定がありません');
        $this->protocol->callTokenVerifier()->verify($token);
    }

    /**
     * 認証処理
     * @param AuthItem $item
     * @return bool true:認証成功, false:認証失敗
     */
    public function authorize(#[\SensitiveParameter] AuthItem $item): bool
    {
        // プロトコルが無い場合は認証失敗
        return $this->protocol?->authorize($item) === true;
    }

    /**
     * 認証解除処理
     * @return bool true:処理成功
     */
    public function deAuthorize(): bool
    {
        // プロトコルが無い場合は処理失敗
        return $this->protocol?->deAuthorize() === true;
    }

    /**
     * 認証のチェック
     * 認証できていれば期間の延長
     * @param AuthItem|null $item
     * @return bool true:チェック成功, false:チェック失敗
     * @throws AuthenticationException
     */
    public function isAuthenticated(#[\SensitiveParameter] AuthItem|null $item = null): bool
    {
        // プロトコルが無い場合はチェック失敗
        return $this->protocol?->isAuthenticated($item) === true;
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
