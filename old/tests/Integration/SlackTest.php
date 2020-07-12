<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2019, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Test\Integration;

use Citrus\Configure\ConfigureException;
use Citrus\Integration\Slack;
use PHPUnit\Framework\TestCase;

/**
 * 外部統合Slack処理のテスト
 */
class SlackTest extends TestCase
{
    /**
     * @test
     * @throws ConfigureException
     */
    public function 設定を読み込んで適用できる()
    {
        // 設定ファイル
        $configures = require(dirname(__DIR__) . '/citrus-configure.php');

        // 生成
        /** @var Slack $slack */
        $slack = Slack::sharedInstance()->loadConfigures($configures);

        // 検証
        $this->assertSame($configures['default']['slack']['hogehoge']['webhook_url'], $slack->webhookURL('hogehoge'));
        $this->assertSame($configures['default']['slack']['fugafuga']['webhook_url'], $slack->webhookURL('fugafuga'));
    }
}
