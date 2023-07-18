<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Session;

use Citrus\Variable\Binders;

/**
 * セッションアイテム
 */
class Item
{
    use Binders;

    /**
     * constructor.
     *
     * @param Item|array|null $session
     */
    public function __construct(Item|array|null $session = null)
    {
        // is null
        if (true === is_null($session))
        {
            return;
        }

        if ($session instanceof Item)
        {
            $this->bindArray($session->properties());
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
     * session value add method
     *
     * @param string $key
     * @param mixed  $value
     * @return void
     */
    public function add(string $key, $value): void
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
}
