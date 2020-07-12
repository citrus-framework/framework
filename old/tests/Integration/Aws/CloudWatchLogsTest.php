<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2019, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Test\Integration\Aws;

use Citrus\Configure\ConfigureException;
use Citrus\Integration\Aws\CloudwatchLogs;
use PHPUnit\Framework\TestCase;

/**
 * CloudWatchLogs処理のテスト
 */
class CloudWatchLogsTest extends TestCase
{
    /**
     * @test
     * @throws ConfigureException
     */
    public function 設定を読み込んで適用できる()
    {
        // 設定ファイル
        $configures = require(dirname(__DIR__) . '/../citrus-configure.php');

        // 生成
        /** @var CloudWatchLogs $cwl */
        $cwl = CloudWatchLogs::getInstance()->loadConfigures($configures);

        // 検証
        $this->assertSame($configures['default']['aws']['cloudwatchlogs'], $cwl->configures['cloudwatchlogs']);
    }
}
