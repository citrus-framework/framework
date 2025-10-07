<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Test;

use Citrus\Configure\ConfigureException;
use Citrus\Gateway;
use PHPUnit\Framework\TestCase;

/**
 * ゲートウェイ処理のテスト
 */
class GatewayTest extends TestCase
{
    /**
     * @test
     */
    public function main_command_想定通り()
    {
        $result = exec('cd tests; ./command.php --domain=example.com --command=Sample\\\SampleData');
        $this->assertSame('execute!', $result);
    }

    /**
     * @test
     * @throws ConfigureException
     */
    public function loadConfigures_設定を読み込んで適用できる()
    {
        // 設定ファイル
        $configures = require(dirname(__DIR__) . '/tests/citrus-configure.php');

        // 生成(例外が発生しない)
        Gateway::sharedInstance()->loadConfigures($configures);
    }
}
