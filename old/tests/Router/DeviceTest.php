<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2019, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Test\Database;

use Citrus\Configure\ConfigureException;
use Citrus\Router\Device;
use PHPUnit\Framework\TestCase;

/**
 * デバイス設定のテスト
 */
class DeviceTest extends TestCase
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
        /** @var Device $device */
        $device = Device::sharedInstance()->loadConfigures($configures);

        // 検証
        $device_list = [
            'default',
            'pc',
            'ipad',
            'xhr',
            'iphone',
            'android',
            'smartphone',
            'mobile',
        ];
        foreach ($device_list as $one)
        {
            $this->assertSame($configures['default']['device'][$one], $device->device_routes[$one]);
        }
    }
}
