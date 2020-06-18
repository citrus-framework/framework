<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus;

/**
 * 共通オブジェクト
 *
 * @deprecated
 */
class Struct
{
    /**
     * compare object
     *
     * @param mixed $object
     * @return bool
     */
    public function equals($object): bool
    {
        return ($this === $object);
    }



    /**
     * obeject vars getter
     *
     * @return array
     */
    public function properties(): array
    {
        return get_object_vars($this);
    }



    /**
     * obeject vars serialize getter
     *
     * @return string
     */
    public function serialize(): string
    {
        return serialize($this);
    }



    /**
     * class name getter
     *
     * @return string
     */
    public function getClass(): string
    {
        return get_class($this);
    }



    /**
     * instance clone getter method
     *
     * @return Struct
     */
    public function getClone()
    {
        return clone $this;
    }



    /**
     * general getter method
     *
     * @param  string $key
     * @return mixed
     */
    public function get($key)
    {
        if (isset($this->$key) === true)
        {
            return $this->$key;
        }
        return null;
    }



    /**
     * general setter method
     *
     * @param mixed $key
     * @param mixed $value
     * @param bool  $strict
     */
    public function set($key, $value, bool $strict = false)
    {
        if ($strict === true)
        {
            if (property_exists($this, $key) === true)
            {
                $this->$key = $value;
            }
        }
        else
        {
            $this->$key = $value;
        }
    }



    /**
     * general adder method
     *
     * @param string $key
     * @param mixed  $value
     */
    public function add($key, $value)
    {
        $add = &$this->$key;

        if ($add == null)
        {
            if (true === is_array($value))
            {
                $add = $value;
            }
            else
            {
                $add = [$value];
            }
        }
        else if (true === is_array($add))
        {
            if (true === is_array($value))
            {
                $add += $value;
            }
            else
            {
                array_push($add, $value);
            }
        }
        else if (is_array($add) === false)
        {
            $add = [$add, $value];
        }
    }



    /**
     * general remover method
     *
     * @param array|string $key
     */
    public function remove($key)
    {
        if (is_array($key) === true)
        {
            foreach ($key as $one)
            {
                unset($this->$one);
            }
        }
        else
        {
            unset($this->$key);
        }
    }



    /**
     * general remover method is empty
     *
     * @param array|string $key
     */
    public function removeIsEmpty($key)
    {
        if (is_array($key) === true)
        {
            foreach ($key as $one)
            {
                if (empty($this->$one) === true)
                {
                    unset($this->$one);
                }
            }
        }
        else
        {
            if (empty($this->$key) === true)
            {
                unset($this->$key);
            }
        }
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
        if (is_null($array) === true)
        {
            return;
        }
        foreach ($array as $ky => $vl)
        {
            $this->set($ky, $vl, $strict);
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
        if (is_null($object) === true)
        {
            return;
        }
        $array = get_object_vars($object);
        $this->bindArray($array, $strict);
    }



    /**
     * get value from context path
     *
     * @param string $context
     * @return mixed
     */
    public function getFromContext($context)
    {
        $context_list = explode('.', $context);
        $context_size = count($context_list);
        $context_get_limit = $context_size - 1;

        $object = $this;
        for ($i = 1; $i <= $context_get_limit; $i++)
        {
            $method = 'get';
            $method_properties = explode('_', $context_list[$i]);
            foreach ($method_properties as $one)
            {
                $method .= ucfirst(strtolower($one));
            }
            if (method_exists($object, $method) === true)
            {
                $object = $object->$method();
            }
            else
            {
                $object = $object->get($context_list[$i]);
            }
        }

        return $object;
    }



    /**
     * set value from context path
     *
     * @param string $context
     * @param mixed  $value
     */
    public function setFromContext(string $context, $value)
    {
        // condition.rowid -> [ 'condition', 'rowid' ]
        $context_list = explode('.', $context);
        $context_size = count($context_list);

        $object = $this;
        foreach ($context_list as $idx => $property_name)
        {
            // 設定したいオブジェクト
            if ($idx === ($context_size - 1))
            {
                $object->set($property_name, $value);
                break;
            }

            // 階層を下げる
            $target = $object->get($property_name);
            if (is_null($target) === true)
            {
                $method_name = 'call' . ucfirst($property_name);
                $target = $object->$method_name();
            }
            if (is_null($target) === true)
            {
                Logger::error('[%s]はnullのプロパティです', $property_name);
                break;
            }
            $object = $target;
        }
    }



    /**
     * obeject vars getter of not null property
     */
    public function notNullProperties()
    {
        $properties = $this->properties();

        $results = [];
        foreach ($properties as $ky => $vl)
        {
            if (is_null($vl) === false)
            {
                $results[$ky] = $vl;
            }
        }
        return $results;
    }
}
