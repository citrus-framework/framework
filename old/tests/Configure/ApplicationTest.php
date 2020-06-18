<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2019, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Test\Configure;

use Citrus\Configure\Application;
use Citrus\Configure\ConfigureException;
use PHPUnit\Framework\TestCase;

/**
 * アプリケーション定義のテスト
 */
class ApplicationTest extends TestCase
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
        /** @var Application $application */
        $application = Application::sharedInstance()->loadConfigures($configures);

        // 検証
        $this->assertSame($configures['default']['application']['id'], $application->id);
        $this->assertSame($configures['default']['application']['path'], $application->path);
        $this->assertSame($configures['example.com']['application']['name'], $application->name);
        $this->assertSame($configures['example.com']['application']['copyright'], $application->copyright);
        $this->assertSame($configures['example.com']['application']['domain'], $application->domain);
    }
}
