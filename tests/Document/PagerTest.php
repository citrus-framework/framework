<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Test\Document;

use Citrus\Configure\ConfigureException;
use Citrus\Document\Pager;
use Citrus\Router;
use PHPUnit\Framework\TestCase;

/**
 * ページャーのテスト
 */
class PagerTest extends TestCase
{
    /**
     * @test
     */
    public function build_想定通り()
    {
        // パターン1(通常)
        $pager = new Pager(2, 77, 10, 5);
        $this->assertEquals([
            'first' => 1,
            'prev' => 1,
            'current' => 2,
            'next' => 3,
            'last' => 8,
            'range' => 5,
            'total' => 77,
            'limit' => 10,
            'views' => [
                1,
                2,
                3,
                4,
            ],
            'item_from' => 11,
            'item_to' => 20,
        ], get_object_vars($pager));

        // パターン2(0件)
        $pager = new Pager(2, 0, 10, 5);
        $this->assertEquals([
            'first' => 1,
            'prev' => null,
            'current' => 1,
            'next' => null,
            'last' => 1,
            'range' => 5,
            'total' => 0,
            'limit' => 10,
            'views' => [
                1
            ],
            'item_from' => 0,
            'item_to' => 0,
        ], get_object_vars($pager));

        // パターン3(最終ページ)
        $pager = new Pager(8, 77, 10, 5);
        $this->assertEquals([
            'first' => 1,
            'prev' => 7,
            'current' => 8,
            'next' => null,
            'last' => 8,
            'range' => 5,
            'total' => 77,
            'limit' => 10,
            'views' => [
                6,
                7,
                8,
            ],
            'item_from' => 71,
            'item_to' => 77,
        ], get_object_vars($pager));
    }
}
