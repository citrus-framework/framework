<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitronIssue All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.besidesplus.net/
 */

namespace Citrus\Authentication;

use Citrus\Authentication;
use Citrus\CitrusException;
use Citrus\Collection;
use Citrus\Database\Connection\Connection;
use Citrus\Intersection;
use Citrus\Logger;
use Citrus\Query\Builder;
use Citrus\Session;
use Citrus\Variable\Strings;

/**
 * JWT認証
 *
 * @see https://jwt.io/
 */
class JWT extends Protocol
{
    /** HMAC using SHA-256 hash */
    public const HS256 = 'HS256';

    /** HMAC using SHA-384 hash */
    public const HS384 = 'HS384';

    /** HMAC using SHA-512 hash */
    public const HS512 = 'HS512';

    /** RSA using SHA-256 hash */
    public const RS256 = 'RS256';

    /** RSA using SHA-384 hash */
    public const RS384 = 'RS384';

    /** RSA using SHA-512 hash */
    public const RS512 = 'RS512';

    /** RSA method */
    private const METHOD_RSA = 'openssl_sign';

    /** HMAC method */
    private const METHOD_HMAC = 'hash_hmac';

    /** @var array アルゴリズムリスト */
    public static array $ALGORITHM_METHODS = [
        self::HS256 => ['hash' => 'SHA256', 'method' => self::METHOD_HMAC],
        self::HS384 => ['hash' => 'SHA384', 'method' => self::METHOD_HMAC],
        self::HS512 => ['hash' => 'SHA512', 'method' => self::METHOD_HMAC],
        self::RS256 => ['hash' => OPENSSL_ALGO_SHA256, 'method' => self::METHOD_RSA],
        self::RS384 => ['hash' => OPENSSL_ALGO_SHA384, 'method' => self::METHOD_RSA],
        self::RS512 => ['hash' => OPENSSL_ALGO_SHA512, 'method' => self::METHOD_RSA],
    ];

    /** @var Connection */
    public Connection $connection;

    /** @var string 秘密鍵 */
    private static string $SECRET_KEY = '9b3DdFJYdIP2Cf6OVPrkhBQUpAjHb3Z2G86rw6HSIJg=';

    /** @var string アルゴリズム */
    private static string $ALGORITHM = self::HS256;

    /** @var int 認証有効期限(秒) */
    private static int|float $EXPIRE_SEC = (24 * 60 * 60);



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
     * JWTエンコード処理してトークンを得る
     *
     * @param array $add_payloads 追加ペイロード
     * @return string JWTトークン
     */
    public function encode(array $add_payloads): string
    {
        // アルゴリズム
        $algorithm = self::$ALGORITHM;
        // 署名文字列
        $secret = self::$SECRET_KEY;
        // 現時刻(秒)
        $now = time();

        // 要素
        $elements = [];

        // ヘッダー
        $elements[] = self::base64encode(json_encode([
            'alg' => $algorithm,
            'typ' => 'JWT',
        ]));

        // ペイロード
        $payloads = Collection::stream([
            // 発行者識別子
            'iss' => 'CitrusIssue3',
            // JWTの有効期限 (現在時刻 + 有効期限)
            'exp' => $this->generateExpiredAt($now),
            // JWTが有効となる開始日時
            'ndf' => $now,
            // JWTの発行日時
            'iat' => $now,
        ])->betterMerge($add_payloads)->toList();
        $elements[] = self::base64encode(json_encode($payloads));

        // 署名
        $elements[] = self::base64encode(
            self::signing(implode('.', $elements), $algorithm, $secret)
        );

        return implode('.', $elements);
    }

    /**
     * JWTトークンをデコードしてペイロードを得る
     *
     * @param string $jwt_token JWTトークン
     * @return array ペイロード配列
     * @throws JWTException
     */
    public function decode(string $jwt_token): array
    {
        // アルゴリズム
        $algorithm = self::$ALGORITHM;
        // 署名文字列
        $secret = self::$SECRET_KEY;
        // 現時刻(秒)
        $now = time();

        // トークン配列の分割
        $tokens = explode('.', $jwt_token);
        if (3 !== count($tokens))
        {
            throw new JWTException('トークン要素数が不足しています');
        }

        // ヘッダーチェック
        $header = json_decode(self::base64decode($tokens[0]), true);
        // 認証アルゴリズムが指定したものではない
        if ($algorithm !== $header['alg'])
        {
            throw new JWTException('認証アルゴリズムが一致しません');
        }
        // 認証アルゴリズムが指定したものではない
        if ('JWT' !== $header['typ'])
        {
            throw new JWTException('認証タイプが一致しません');
        }

        // ペイロードチェック
        $payload = json_decode(self::base64decode($tokens[1]), true);
        // 有効期限設定が無い、もしくは現在時刻より以前に設定されている
        if (false === isset($payload['exp']) or $payload['exp'] < $now)
        {
            throw new JWTException('有効期限切れの認証トークンです');
        }

        // 署名チェック
        $signature = self::base64decode($tokens[2]);
        $verifying_token = sprintf('%s.%s', $tokens[0], $tokens[1]);
        // 署名が有効ではない
        if (false === self::verifySignature($verifying_token, $signature, $secret, $algorithm))
        {
            throw new JWTException('署名が有効ではありません');
        }

        return $payload;
    }

    /**
     * BASE64エンコード
     *
     * @param string $message 対象文字列
     * @return string
     */
    public static function base64encode(string $message): string
    {
        return str_replace('=', '', strtr(base64_encode($message), '+/', '-_'));
    }

    /**
     * BASE64デコード
     *
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
     *
     * @param int $timestamp 起点になるUNIXタイムスタンプ
     * @return int 有効期限のUNIXタイムスタンプの取得
     */
    public function generateExpiredAt(int $timestamp): int
    {
        return ($timestamp + self::$EXPIRE_SEC);
    }

    /**
     * {@inheritdoc}
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
        $item->token = $this->encode(['user_id' => $item->user_id]);
        $item->expired_at = date('Y-m-d H:i:s', $this->generateExpiredAt(time()));
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
     * {@inheritdoc}
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
    public function isAuthenticated(AuthItem|null $item = null): bool
    {
        // 指定されない場合はsessionから取得
        $item ??= Session::$session->call(Authentication::SESSION_KEY);
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
            Logger::debug(
                'ログアウト:ユーザIDが無い(user_id=%s)、もしくはトークンが無い(token=%s)、もしくはタイムアウト(expired_at=%s)',
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
            Logger::debug(
                'ログアウト:タイムアウト(%s) < 現在時間(%s)',
                $expired_ts,
                $now_ts
            );
            return false;
        }

        // 対象テーブル
        $table_name = Authentication::$AUTHORIZE_TABLE_NAME;

        // まだ認証済みなので、認証期間の延長
        $authentic = new AuthItem();
        $authentic->expired_at = date('Y-m-d H:i:s', $this->generateExpiredAt(time()));
        $condition = new AuthItem();
        $condition->user_id = $item->user_id;
        $condition->token = $item->token;
        // 更新
        $result = (new Builder($this->connection))->update($table_name, $authentic, $condition)->execute();

        // 時間を延長
        /** @var AuthItem $item */
        $item = (new Builder($this->connection))->select($table_name, $condition)->execute(AuthItem::class)->one();
        Session::$session->add(Authentication::SESSION_KEY, $item);
        Session::commit();

        return ($result > 0);
    }

    /**
     * 署名の生成
     *
     * @param string $unsigned_token 未署名トークン
     * @param string $algorithm      アルゴリズム
     * @param string $secret         署名文字列
     * @return string
     */
    private static function signing(string $unsigned_token, string $algorithm, string $secret): string
    {
        // メソッド
        $method = self::$ALGORITHM_METHODS[$algorithm]['method'];
        // ハッシュ
        $hash = self::$ALGORITHM_METHODS[$algorithm]['hash'];

        /** @var string $signature 署名 */
        $signature = Intersection::fetch($method, [
            // HMAC
            self::METHOD_HMAC => function () use ($hash, $unsigned_token, $secret) {
                return hash_hmac($hash, $unsigned_token, $secret, true);
            },
            // RSA
            self::METHOD_RSA => function () use ($hash, $unsigned_token, $secret) {
                $signature = '';
                $success = openssl_sign($unsigned_token, $signature, $secret, $hash);
                if (false === $success)
                {
                    throw new CitrusException('OpenSSL signing Error');
                }
                return $signature;
            },
        ], true);

        return ($signature ?? '');
    }

    /**
     * 署名の確認
     *
     * @param string $verifying_token 確認したいトークン
     * @param string $signature       署名
     * @param string $secret          シークレットキー
     * @param string $algorithm       アルゴリズム
     * @return bool true:確認OK,false:確認NG
     */
    private static function verifySignature(
        string $verifying_token,
        string $signature,
        string $secret,
        string $algorithm
    ): bool {
        // メソッド
        $method = self::$ALGORITHM_METHODS[$algorithm]['method'];
        // ハッシュ
        $hash = self::$ALGORITHM_METHODS[$algorithm]['hash'];

        // 署名の確認ができたかどうかを返却
        return Intersection::fetch($method, [
            // HMAC
            self::METHOD_HMAC => function () use ($verifying_token, $signature, $secret, $hash) {
                return (true === hash_equals($signature, hash_hmac($hash, $verifying_token, $secret, true)));
            },
            // RSA
            self::METHOD_RSA => function () use ($verifying_token, $signature, $secret, $hash) {
                return (1 === openssl_verify($verifying_token, $signature, $secret, $hash));
            },
        ], true);
    }
}
