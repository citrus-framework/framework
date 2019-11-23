<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Query;

use Citrus\Collection;

/**
 * クエリ最適化処理群
 */
trait Optimize
{
    /**
     * クエリに定義されていないパラメータを消す
     *
     * @param string     $query
     * @param array|null $parameters
     * @return array
     */
    public static function optimizeParameter(string $query, array $parameters = null): array
    {
        // パラメータがなければスルー
        if (true === is_null($parameters))
        {
            return $parameters;
        }

        // conditionの削除
        unset($parameters[':condition']);

        // パラメータを最適化して返却
        return Collection::stream($parameters)->filter(function ($ky, $vl) use ($query) {
            return (false !== strpos($query, $ky));
        })->toList();
    }
}
