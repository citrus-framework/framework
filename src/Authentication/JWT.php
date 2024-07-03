<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitronIssue All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.besidesplus.net/
 */

namespace Citrus\Authentication;

use Citrus\Collection;
use Citrus\Configure;
use Citrus\Configure\Configurable;
use Citrus\Configure\ConfigureException;
use Citrus\Database\Connection\Connection;
use Citrus\Intersection;
use Citrus\Variable\Hash;
use Citrus\Variable\Hash\AlgorithmType;
use Citrus\Variable\Hash\HashException;
use Citrus\Variable\Hash\HashLogic;
use Citrus\Variable\Hash\MethodType;

/**
 * JWT認証
 * @see https://jwt.io/
 */
class JWT extends Configurable
{
    /**
     * @param Connection     $connection
     * @param HashLogic|null $hashLogic      ハッシュロジック
     * @param int|null       $expiration_sec 認証の有効秒数
     * @param int|null       $now            処理用現在時刻
     * @throws ConfigureException
     */
    public function __construct(
        public Connection $connection,
        private HashLogic|null $hashLogic = null,
        private int|null $expiration_sec = (24 * 60 * 60),
        private int|null $now = null,
    ) {
        // 設定の読み込み
        $this->loadConfigures(Configure::callConfigures());
        // 現在時刻
        $this->now = time();
    }

    /**
     * {@inheritDoc}
     */
    public function loadConfigures(array $configures = []): Configurable
    {
        // 設定配列の読み込み
        parent::loadConfigures($configures);

        // ハッシュロジックの設定
        $this->hashLogic = Hash::logic($this->configures['method'], $this->configures['algorithm']);
        // シークレットの設定
        $this->hashLogic->secret = $this->configures['secret'];
        // 認証の有効秒数
        $this->expiration_sec = $this->configures['expiration_sec'];

        return $this;
    }

    /**
     * JWTエンコード処理してトークンを得る
     * @param array $add_payloads 追加ペイロード
     * @return string JWTトークン
     * @throws HashException
     */
    public function encode(array $add_payloads): string
    {
        // 現時刻(秒)
        $now = $this->now;

        // 要素
        $elements = [];

        // ヘッダー
        $elements[] = self::base64encode(json_encode([
            'alg' => $this->callJwtAlgorithm(),
            'typ' => 'JWT',
        ]));

        // ペイロード
        $payloads = Collection::stream([
            // 発行者識別子
            'iss' => 'CitrusFramework3',
            // JWTの有効期限 (現在時刻 + 有効期限)
            'exp' => $this->callExpiredAt($now),
            // JWTが有効となる開始日時
            'ndf' => $now,
            // JWTの発行日時
            'iat' => $now,
        ])->betterMerge($add_payloads)->toList();
        $elements[] = self::base64encode(json_encode($payloads));

        // 未署名トークンを設定
        $this->hashLogic->token = implode('.', $elements);

        // 署名
        $elements[] = self::base64encode($this->hashLogic->signature());

        return implode('.', $elements);
    }

    /**
     * JWTトークンをデコードしてペイロードを得る
     * @param string $jwt_token JWTトークン
     * @return array ペイロード配列
     * @throws JWTException
     */
    public function decode(string $jwt_token): array
    {
        // 現時刻(秒)
        $now = $this->now;

        // トークン配列の分割
        $tokens = explode('.', $jwt_token);
        JWTException::exceptionIf(
            3 !== count($tokens),
            'トークン要素数が不足しています'
        );

        // ヘッダーチェック
        $header = json_decode(self::base64decode($tokens[0]), true);
        // 認証アルゴリズムが指定したものではない
        JWTException::exceptionElse(
            $this->callJwtAlgorithm() === $header['alg'],
            sprintf('認証アルゴリズムが一致しません、「%s」になっています。', $header['alg']),
        );
        // 認証アルゴリズムが指定したものではない
        JWTException::exceptionElse(
            'JWT' === $header['typ'],
            sprintf('認証タイプがJWTではありません、「%s」になっています。', $header['typ']),
        );

        // ペイロードチェック
        $payload = json_decode(self::base64decode($tokens[1]), true);
        // 有効期限設定が無い、もしくは現在時刻より以前に設定されている
        JWTException::exceptionIf(
            false === array_key_exists('exp', $payload) or $payload['exp'] < $now,
            '有効期限切れの認証トークンです',
        );

        // 署名チェック
        $signature = self::base64decode($tokens[2]);
        $this->hashLogic->token = sprintf('%s.%s', $tokens[0], $tokens[1]);
        // 署名が有効ではない
        JWTException::exceptionElse(
            $this->hashLogic->verify($signature),
            '署名が有効ではありません'
        );

        return $payload;
    }

    /**
     * BASE64エンコード
     * @param string $message 対象文字列
     * @return string
     */
    public static function base64encode(string $message): string
    {
        return str_replace('=', '', strtr(base64_encode($message), '+/', '-_'));
    }

    /**
     * BASE64デコード
     * @param string $message 対象文字列
     * @return string
     */
    public static function base64decode(string $message): string
    {
        // 字詰めの必要はあるか
        $remainder = strlen($message) % 4;
        if (0 < $remainder)
        {
            $message .= str_repeat('=', (4 - $remainder));
        }
        return base64_decode(strtr($message, '-_', '+/'));
    }

    /**
     * 有効期限を取得
     * @param int|null $timestamp 起点になるUNIXタイムスタンプ
     * @return int 有効期限のUNIXタイムスタンプの取得
     */
    public function callExpiredAt(int|null $timestamp = null): int
    {
        return ($timestamp ?? $this->now + $this->expiration_sec);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override] protected function configureDefaults(): array
    {
        return [
            'method'         => MethodType::HMAC,
            'algorithm'      => AlgorithmType::SHA256,
            'secret'         => 'secret',
            'expiration_sec' => (28 * 24 * 60 * 60), // 4週
        ];
    }

    /**
     * {@inheritDoc}
     */
    #[\Override] protected function configureRequires(): array
    {
        return [
            'method',
            'algorithm',
            'secret',
            'expiration_sec',
        ];
    }

    /**
     * JWT用のアルゴリズム文字列を生成
     * @return string
     */
    private function callJwtAlgorithm(): string
    {
        $method_string = Intersection::fetch(get_class($this->hashLogic), [
            Hash\Hmac::class => 'HS',
            Hash\Rsa::class  => 'RS',
        ]);
        $algorithm_string = Intersection::fetch($this->hashLogic->algorithmType->value, [
            AlgorithmType::SHA256->value => '256',
            AlgorithmType::SHA384->value => '384',
            AlgorithmType::SHA512->value => '512',
        ]);

        return $method_string . $algorithm_string;
    }
}
