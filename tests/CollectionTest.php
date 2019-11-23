<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2019, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Test;

use Citrus\Collection;
use PHPUnit\Framework\TestCase;

/**
 * コレクションクラスのテスト
 */
class CollectionTest extends TestCase
{
    /**
     * @test
     */
    public function betterMerge_両方の要素を残したいい感じの配列マージ()
    {
        $array1 = [
            'a' => 1,
            'b' => 2,
            'c' => [
                'd' => 3,
                'e' => 4,
            ],
            'f' => 5,
        ];
        $array2 = [
            'a' => 5,
            'c' => [
                'g' => 6,
            ],
            'h' => 7,
        ];

        $expected = [
            'a' => 5,
            'b' => 2,
            'c' => [
                'd' => 3,
                'e' => 4,
                'g' => 6,
            ],
            'f' => 5,
            'h' => 7,
        ];

        // いい感じのマージ
        $actual = Collection::stream($array1)->betterMerge($array2)->toList();

        // 検算
        $this->assertSame($expected, $actual);
    }



    /**
     * @test
     */
    public function filter_指定データのみ残した配列生成()
    {
        $values = [
            'a' => 1,
            'b' => 2,
            'c' => 3,
            'd' => 4,
        ];

        // キー「c」以外を残す
        $expected1 = [
            'a' => 1,
            'b' => 2,
            'd' => 4,
        ];
        // 検算
        $this->assertSame($expected1, Collection::stream($values)->filter(function ($ky, $vl) {
            return ('c' !== $ky);
        })->toList());

        // 値「2」を超えるものだけ残す
        $expected2 = [
            'c' => 3,
            'd' => 4,
        ];
        // 検算
        $this->assertSame($expected2, Collection::stream($values)->filter(function ($ky, $vl) {
            return (2 < $vl);
        })->toList());
    }



    /**
     * @test
     */
    public function append_データ生成できたものだけで配列生成()
    {
        $values = [
            'a' => 1,
            'b' => 2,
            'c' => 3,
            'd' => 4,
        ];

        // キー「c」以外を残して、全部1を足す
        $expected = [
            (1 + 1),
            (2 + 1),
            (4 + 1),
        ];
        // 検算
        $this->assertSame($expected, Collection::stream($values)->append(function ($ky, $vl) {
            if ('c' !== $ky)
            {
                return ($vl + 1);
            }
            return null;
        })->toList());
    }
}
