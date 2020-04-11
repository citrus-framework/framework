<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Session;

use Citrus\Variable\Structs;

/**
 * セッションアイテム
 */
class Item
{
    use Structs;

    /**
     * constructor.
     *
     * @param Item|null $session
     */
    public function __construct($session = null)
    {
        // is null
        if (true === is_null($session))
        {
            return;
        }

        if ($session instanceof Item)
        {
            $this->bind($session->properties());
            return;
        }

        // ループできれば設定していく
        foreach ($session as $ky => $vl)
        {
            $this->$ky = serialize($vl);
        }
    }



    /**
     * session value parse method
     *
     * @param Item $element
     * @return void
     */
    public function parseItem(Item $element): void
    {
        $this->bindObject($element);
    }



    /**
     * session value regist method
     *
     * @param string $key
     * @param mixed  $value
     * @return void
     */
    public function regist(string $key, $value): void
    {
        $this->$key = serialize($value);
    }



    /**
     * session value call
     *
     * @param string $key
     * @return mixed|null
     */
    public function call(string $key)
    {
        if (true === isset($this->$key))
        {
            return unserialize($this->$key);
        }
        return null;
    }



    /**
     * session value calls
     *
     * @return mixed[]
     */
    public function properties(): array
    {
        $result = [];
        $property_keys = array_keys(get_object_vars($this));
        foreach ($property_keys as $one)
        {
            $result[$one] = $this->call($one);
        }
        return $result;
    }



    /**
     * general bind method
     *
     * @param array|null $array
     * @param bool|null  $strict
     * @return void
     */
    public function bind(?array $array = null, ?bool $strict = false): void
    {
        $this->bindArray($array, $strict);
    }



    /**
     * general bind array method
     *
     * @param array|null $array
     * @param bool|null  $strict
     * @return void
     */
    public function bindArray(?array $array = null, ?bool $strict = false): void
    {
        if (true === is_null($array))
        {
            return;
        }
        foreach ($array as $ky => $vl)
        {
            $this->set($ky, serialize($vl), $strict);
        }
    }



    /**
     * general bind object method
     *
     * @param mixed|null $object
     * @param bool|null  $strict
     * @return void
     */
    public function bindObject($object = null, ?bool $strict = false): void
    {
        if (true === is_null($object))
        {
            return;
        }
        $array = get_object_vars($object);
        $this->bindArray($array, $strict);
    }
}
