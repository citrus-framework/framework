<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Cache;

/**
 * サーバーデーモンタイプのキャッシュ
 */
abstract class Deamon implements Engine
{
    /** @var \Redis|\Memcached handler */
    protected $handler;

    /** @var string host */
    public $host;

    /** @var int port */
    public $port;

    /** @var string prefix */
    public $prefix;

    /** @var int expire second */
    public $expire;



    /**
     * constructor.
     *
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
     *
     * @return void
     */
    abstract public function connect(): void;



    /**
     * 切断
     *
     * @return void
     */
    abstract public function disconnect(): void;



    /**
     * 基本的にはドメイン付きのキーを返す
     *
     * prefix <= 'hogehoge.com'
     * key    <= 'productSummaries'
     * => hogehoge.com:productSummaries
     *
     * ドメインがない場合は :productSummaries となるが、ドメインなしを明示的にしたいので : は捨てない
     *
     * @param string    $key
     * @param bool|null $with_hash
     * @return string
     */
    public function callPrefixedKey(string $key, bool $with_hash = false): string
    {
        if (false === is_string($key))
        {
            $key = serialize($key);
        }

        if (true === $with_hash)
        {
            $key = md5($key);
        }

        return sprintf('%s:%s', $this->prefix, $key);
    }
}
