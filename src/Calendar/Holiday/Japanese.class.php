<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2018, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Calendar\Holiday;

use Citrus\Variable\Structs;

class Japanese
{
    use Structs;

    const HOLIDAYS = [
        2018 => [
            1 => [
                1 => '元日',
                8 => '成人の日',
                11 => '建国記念の日',
            ],
            2 => [
                12 => '休日',
            ],
            3 => [
                21 => '春分の日',
            ],
            4 => [
                29 => '昭和の日',
                30 => '休日',
            ],
            5 => [
                3 => '憲法記念日',
                4 => 'みどりの日',
                5 => 'こどもの日',
            ],
            6 => [

            ],
            7 => [
                16 => '海の日',
            ],
            8 => [
                11 => '山の日',
            ],
            9 => [
                17 => '敬老の日',
                23 => '秋分の日',
                24 => '休日',
            ],
            10 => [
                8 => '体育の日',
            ],
            11 => [
                3 => '文化の日',
                23 => '勤労感謝の日',
            ],
            12 => [
                23 => '天皇誕生日',
                24 => '休日',
            ],
        ],
        2019 => [
            1 => [
                1 => '元日',
                14 => '成人の日',
            ],
            2 => [
                11 => '建国記念の日',
            ],
            3 => [
                21 => '春分の日',
            ],
            4 => [
                29 => '昭和の日',
            ],
            5 => [
                3 => '憲法記念日',
                4 => 'みどりの日',
                5 => 'こどもの日',
                6 => '休日',
            ],
            6 => [
            ],
            7 => [
                15 => '海の日',
            ],
            8 => [
                11 => '山の日',
                12 => '休日',
            ],
            9 => [
                16 => '敬老の日',
                23 => '秋分の日',
            ],
            10 => [
                14 => '体育の日',
            ],
            11 => [
                3 => '文化の日',
                4 => '休日',
                23 => '勤労感謝の日',
            ],
            12 => [
            ],
        ],
    ];



    /**
     * 祝日かどうか
     * (2018 - 2019)
     *
     * @param string $date 日付文字列
     * @return bool true:祝日,false:祝日ではない
     */
    public static function isHoliday(string $date): bool
    {
        $timestamp = strtotime($date);
        $year = date('Y', $timestamp);
        $month = date('n', $timestamp);
        $day = date('j', $timestamp);

        if (true === isset(self::HOLIDAYS[$year])
            && true === isset(self::HOLIDAYS[$year][$month])
            && true === isset(self::HOLIDAYS[$year][$month][$day]))
        {
            return true;
        }
        return false;
    }
}
