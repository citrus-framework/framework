<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2019, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Test\Integration;

use Citrus\Configure\ConfigureException;
use Citrus\Message;
use Citrus\Message\Item;
use PHPUnit\Framework\TestCase;

/**
 * メッセージ処理のテスト
 */
class MessageTest extends TestCase
{
    /**
     * @test
     * @throws ConfigureException
     */
    public function 設定を読み込んで適用できる()
    {
        // 設定ファイル
        $configures = require(dirname(__DIR__) . '/tests/citrus-configure.php');

        // 生成(例外が発生しない)
        Message::sharedInstance()->loadConfigures($configures);
    }


    /**
     * @test
     * @throws ConfigureException
     */
    public function メッセージ設定と取得できる()
    {
        // 設定ファイル
        $configures = require(dirname(__DIR__) . '/tests/citrus-configure.php');

        // 生成(例外が発生しない)
        Message::sharedInstance()->loadConfigures($configures);

        // メッセージ設定
        $description = '成功した';
        Message::sharedInstance()->addSuccess($description);

        // メッセージ取得
        $items = Message::callItems();
        $this->assertCount(1, $items);
        $item = $items[0];

        // 検算
        $this->assertSame($description, $item->description);
        $this->assertSame(Item::TYPE_SUCCESS, $item->type);
        $this->assertSame(Item::TAG_COMMON, $item->tag);
    }
}
