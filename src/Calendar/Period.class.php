<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2018, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Calendar;

use Citrus\Variable\Structs;

/**
 * カレンダーの旬
 */
class Period
{
    use Structs;

    /** 上旬 */
    const FIRST = 'first';

    /** 中旬 */
    const SECOND = 'second';

    /** 下旬 */
    const LAST = 'last';



    /**
     * タイムスタンプから旬を判定し返却する
     *
     * @param int $timstamp
     * @return string
     */
    public static function periodByTimestamp(int $timstamp): string
    {
        $day = date('j', $timstamp);

        // 10日以前
        if (10 >= $day)
        {
            return self::FIRST;
        }
        // 20日以前
        if (20 >= $day)
        {
            return self::SECOND;
        }
        // それ以外
        return self::LAST;
    }
}
