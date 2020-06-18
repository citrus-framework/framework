<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus;

use Closure;

/**
 * NVL処理
 */
class NVL
{
    /**
     * NVL
     *
     * @param mixed $value
     * @param mixed $replace
     * @return mixed
     */
    public static function NVL($value, $replace)
    {
        if (is_null($value) === false)
        {
            return $value;
        }
        return $replace;
    }



    /**
     * NVL coalesce null
     *
     * @return mixed|null
     */
    public static function coalesceNull()
    {
        $result = null;
        $replacements = func_get_args();

        foreach ($replacements as $replacement)
        {
            if (is_null($replacement) === true)
            {
                continue;
            }

            // クロージャなら実行
            if ($replacement instanceof Closure)
            {
                $result = $replacement();
            }
            else
            {
                $result = $replacement;
            }
            break;
        }

        return $result;
    }



    /**
     * NVL coalesce empty
     *
     * @return mixed|null
     */
    public static function coalesceEmpty()
    {
        $result = null;
        $replacements = func_get_args();

        foreach ($replacements as $replacement)
        {
            if (empty($replacement) === true)
            {
                continue;
            }

            // クロージャなら実行
            if ($replacement instanceof Closure)
            {
                $result = $replacement();
            }
            else
            {
                $result = $replacement;
            }
            break;
        }

        return $result;
    }



    /**
     * ArrayVL
     *
     * @param array  $value
     * @param mixed  $key
     * @param mixed  $replace
     * @return array|mixed
     */
    public static function ArrayVL(array $value, $key, $replace)
    {
        $value = self::NVL($value, $replace);

        $result = $replace;

        if (is_array($value) === true && isset($value[$key]) === true)
        {
            $result = $value[$key];
        }

        return $result;
    }



    /**
     * EmptyVL
     *
     * @param mixed $value
     * @param mixed $replace1
     * @param mixed $replace2
     * @return array|mixed|Closure|null
     */
    public static function EmptyVL($value, $replace1, $replace2)
    {
        $replace = null;
        if (empty($value) === true)
        {
            $replace = $replace1;
        }
        else
        {
            $replace = $replace2;
        }

        // クロージャも許容
        if ($replace instanceof \Closure)
        {
            return $replace();
        }
        return $replace;
    }
}
