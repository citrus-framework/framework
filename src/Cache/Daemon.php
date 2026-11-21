<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Cache;

/**
 * サーバーデーモンタイプのキャッシュ
 */
abstract class Daemon implements Engine
{
    /** @var string host */
    public string $host;

    /** @var int port */
    public int $port;

    /** @var string prefix */
    public string $prefix;

    /** @var int expire second */
    public int $expire;

    /** @var \Redis|\Memcached|null handler */
    protected \Redis|\Memcached|null $handler = null;

    /**
     * constructor.
     * @param string $prefix
     * @param int    $expire
     */
    public function __construct(string $prefix = '', int $expire = 0)
    {
        $this->prefix = $prefix;
        $this->expire = $expire;
    }

    /**
     * destructor
     */
    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * 接続
     * @return void
     */
    abstract public function connect(): void;

    /**
     * 切断
     * @return void
     */
    abstract public function disconnect(): void;

    /**
     * 基本的にはドメイン付きのキーを返す
     *   prefix <= 'hogehoge.com'
     *   key    <= 'productSummaries'
     *   => hogehoge.com:productSummaries
     * ドメインがない場合は :productSummaries となるが、ドメインなしを明示的にしたいので : は捨てない
     * @param string    $key
     * @param bool|null $with_hash
     * @return string
     */
    public function callPrefixedKey(string $key, bool $with_hash = false): string
    {
        if (is_string($key) === false)
        {
            $key = serialize($key);
        }

        if ($with_hash === true)
        {
            $key = md5($key);
        }

        return sprintf('%s:%s', $this->prefix, $key);
    }

    /**
     * {@inheritDoc}
     * @throws CacheException
     */
    public function callWithBind(
        string $key,
        callable $valueFunction,
        int $expire = 0
    ): object|array|string|float|int|bool {
        // あれば返却
        if ($this->exists($key) === true)
        {
            return $this->call($key);
        }

        // 無ければ、ロジックを実行し、保存しておく
        $value = $valueFunction();
        if (is_null($value) === false)
        {
            $this->bind($key, $value, $expire);
        }

        return $value;
    }
}
