<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus;

use Citrus\Cache\Engine;
use Citrus\Cache\Memcached;
use Citrus\Cache\Redis;
use Citrus\Configure\Configurable;
use Citrus\Variable\Singleton;

/**
 * キャッシュ処理
 */
class Cache extends Configurable
{
    use Singleton;

    /** @var string cache engine redis */
    public const ENGINE_REDIS = 'redis';

    /** @var string cache engine memcached */
    public const ENGINE_MEMCACHED = 'memcached';

    /** @var Engine キャッシュエンジン */
    protected $engine;



    /**
     * {@inheritDoc}
     */
    public function loadConfigures(array $configures = []): Configurable
    {
        // 設定配列の読み込み
        parent::loadConfigures($configures);

        // キャッシュエンジン別の設定
        $engine = $this->configures['engine'];
        // キャッシュエンジンがデーモンタイプ
        if (true === self::isTypeDeamon($engine))
        {
            $prefix = $this->configures['prefix'];
            $expire = $this->configures['expire'];
            $host = $this->configures['host'];
            $port = $this->configures['port'];

            $this->engine = Intersection::fetch($engine, [
                // Memcached
                self::ENGINE_MEMCACHED => function () use ($prefix, $expire) {
                    return new Memcached($prefix, $expire);
                },
                // Redis
                self::ENGINE_REDIS => function () use ($prefix, $expire) {
                    return new Redis($prefix, $expire);
                },
            ], true);

            // 設定
            $options = [
                'prefix' => $prefix,
                'expire' => $expire,
                'host' => $host,
                'port' => $port,
            ];
            foreach ($options as $ky => $vl)
            {
                $this->engine->$ky = $vl;
            }
        }

        return $this;
    }



    /**
     * 値の取得
     *
     * @param string $key
     * @return mixed
     */
    public function call(string $key)
    {
        return $this->engine->call($key);
    }



    /**
     * 値の設定
     *
     * @param string $key    キー
     * @param mixed  $value  値
     * @param int    $expire 期限切れまでの時間
     * @return void
     */
    public function bind(string $key, $value, int $expire = 0): void
    {
        $this->engine->bind($key, $value, $expire);
    }


    /**
     * 値の存在確認
     *
     * @param string $key
     * @return bool
     */
    public function exists(string $key): bool
    {
        return $this->engine->exists($key);
    }



    /**
     * 値の取得
     * 存在しない場合は値の設定ロジックを実行し、返却する
     *
     * @param string   $key           キー
     * @param callable $valueFunction 無名関数
     * @param int      $expire        期限切れまでの時間
     * @return mixed
     */
    public function callWithBind(string $key, callable $valueFunction, int $expire = 0)
    {
        return $this->engine->bind($key, $valueFunction(), $expire);
    }



    /**
     * {@inheritDoc}
     */
    protected function configureKey(): string
    {
        return 'cache';
    }



    /**
     * {@inheritDoc}
     */
    protected function configureDefaults(): array
    {
        return [
            'prefix' => '',
            'expire' => (60 * 60 * 24),
        ];
    }



    /**
     * {@inheritDoc}
     */
    protected function configureRequires(): array
    {
        $requires = [
            'engine',
            'expire',
        ];

        // キャッシュエンジン別の設定
        $engine = $this->configures['engine'];
        // Memcached
        // Redis
        if (true === in_array($engine, [self::ENGINE_MEMCACHED, self::ENGINE_REDIS], true))
        {
            $requires = array_merge($requires, ['host', 'port']);
        }
        return $requires;
    }



    /**
     * キャッシュエンジンがデーモンタイプの場合
     *
     * @param string $engine_type キャッシュエンジンのタイプ
     * @return bool true:デーモンタイプ
     */
    private static function isTypeDeamon(string $engine_type): bool
    {
        return in_array($engine_type, [self::ENGINE_MEMCACHED, self::ENGINE_REDIS], true);
    }
}
