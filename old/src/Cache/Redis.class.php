<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Cache;

use Citrus\CitrusException;
use RedisException;

/**
 * Redis接続
 *
 * @property \Redis $handler
 */
class Redis extends Deamon
{
    /**
     * {@inheritDoc}
     */
    public function connect(): void
    {
        $this->handler = new \Redis();
        $this->handler->connect($this->host, $this->port);
    }



    /**
     * {@inheritDoc}
     */
    public function disconnect(): void
    {
        if (is_null($this->handler) === false)
        {
            $this->handler->close();
        }
        $this->handler = null;
    }



    /**
     * {@inheritDoc}
     */
    public function call($key)
    {
        // cache key
        $cache_key = $this->callPrefixedKey($key);

        // serialized value
        $serialized_value = $this->handler->get($cache_key);

        // unserialize and return
        return unserialize($serialized_value);
    }



    /**
     * {@inheritDoc}
     * @throws CitrusException
     */
    public function bind(string $key, $value, int $expire = 0)
    {
        try
        {
            // cache key
            $cache_key = $this->callPrefixedKey($key);

            // serialized value
            $serialized_value = serialize($value);

            // set value
            $result = $this->handler->set($cache_key, $serialized_value);
            if (false === $result)
            {
                throw new CacheException(sprintf('Redis::set に失敗しました。 message=%s', $this->handler->getLastError()));
            }

            // set exprire
            if (0 < $expire)
            {
                $this->handler->expire($cache_key, $expire);
            }
        }
        catch (RedisException $e)
        {
            throw CacheException::convert($e);
        }
        catch (CacheException $e)
        {
            throw $e;
        }
    }



    /**
     * {@inheritDoc}
     */
    public function exists($key): bool
    {
        // cache key
        $cache_key = $this->callPrefixedKey($key);

        return $this->handler->exists($cache_key);
    }



    /**
     * {@inheritDoc}
     * @throws CitrusException
     */
    public function callWithBind($key, callable $valueFunction, int $expire = 0)
    {
        // あれば返却
        $exists = $this->exists($key);
        if (true === $exists)
        {
            return $this->call($key);
        }

        // 無ければ、ロジックを実行し、保存しておく
        $value = $valueFunction();
        if (false === is_null($value))
        {
            $this->bind($key, $value, $expire);
        }

        return $value;
    }
}
