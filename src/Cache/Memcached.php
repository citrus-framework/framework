<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Cache;

use MemcachedException;

/**
 * Memcached接続
 * @property \Memcached $handler
 */
class Memcached extends Daemon
{
    /**
     * {@inheritDoc}
     */
    public function connect(): void
    {
        $this->handler = new \Memcached();
        $this->handler->addServer($this->host, $this->port);
    }

    /**
     * {@inheritDoc}
     */
    public function disconnect(): void
    {
        $this->handler?->quit();
        $this->handler = null;
    }

    /**
     * {@inheritDoc}
     */
    public function call(string $key): object|array|string|float|int|bool|null
    {
        // cache key
        $cache_key = $this->callPrefixedKey($key, true);

        // serialized value
        $serialized_value = $this->handler->get($cache_key);

        // un serialize and return
        return unserialize($serialized_value);
    }

    /**
     * {@inheritDoc}
     * @throws CacheException
     */
    public function bind(string $key, object|array|string|float|int|bool $value, int $expire = 0): void
    {
        try
        {
            // cache key
            $cache_key = $this->callPrefixedKey($key, true);

            // serialized value
            $serialized_value = serialize($value);

            // expire
            if ($expire === 0)
            {
                $expire = $this->expire;
            }
            $expire += time();

            // set value
            $result = $this->handler->set($cache_key, $serialized_value, $expire);
            CacheException::exceptionElse(
                $result,
                sprintf('Memcached::set に失敗しました。 message=%s', $this->handler->getResultMessage())
            );
        }
        catch (MemcachedException $e)
        {
            throw CacheException::convert($e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function exists(string $key): bool
    {
        // 一旦キー取得(キーがあるかどうかで判断、取得するとステータスが発生する)
        $this->call($key);

        return ($this->handler->getResultCode() !== \Memcached::RES_NOTFOUND);
    }
}
