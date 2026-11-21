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
class Item extends \stdClass
{
    use Binders;

    /**
     * constructor.
     * @param Item|array|null $session
     */
    public function __construct(Item|array|null $session = null)
    {
        if ($session instanceof Item)
        {
            $this->bindArray($session->properties());
            return;
        }

        // ループできれば設定していく(null は空の配列になり、何も設定しない)
        foreach ((array)$session as $ky => $vl)
        {
            $this->$ky = serialize($vl);
        }
    }

    /**
     * session value parse method
     * @param Item $element
     * @return void
     */
    public function parseItem(Item $element): void
    {
        $this->bindObject($element);
    }

    /**
     * session value add method
     * @param string                             $key
     * @param object|array|string|float|int|bool $value
     * @return void
     */
    public function add(string $key, object|array|string|float|int|bool $value): void
    {
        $this->$key = serialize($value);
    }

    /**
     * session value call
     * @param string $key
     * @return object|array|string|float|int|bool|null
     */
    public function call(string $key): object|array|string|float|int|bool|null
    {
        if (isset($this->$key) === true)
        {
            return unserialize($this->$key);
        }
        return null;
    }

    /**
     * session value calls
     * @return (object|array|string|float|int|bool|null)[]
     */
    public function properties(): array
    {
        $property_keys = array_keys(get_object_vars($this));
        return array_combine($property_keys, array_map(fn ($one) => $this->call($one), $property_keys));
    }

    /**
     * general bind array method
     * @param array|null $array
     * @param bool|null  $strict
     * @return void
     */
    public function bindArray(array|null $array = null, bool|null $strict = false): void
    {
        // null は空の配列になり、何も設定しない
        foreach ((array)$array as $ky => $vl)
        {
            $this->set($ky, serialize($vl), $strict);
        }
    }
}
