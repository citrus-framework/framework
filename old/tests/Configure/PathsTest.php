<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2019, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Test\Configure;

use Citrus\Configure\ConfigureException;
use Citrus\Configure\Paths;
use PHPUnit\Framework\TestCase;

/**
 * パス定義のテスト
 */
class PathsTest extends TestCase
{
    /**
     * @test
     *
     * @throws ConfigureException
     */
    public function 設定を読み込んで適用できる()
    {
        // 設定ファイル
        $configures = require(dirname(__DIR__) . '/citrus-configure.php');

        // 生成
        /** @var Paths $paths */
        $paths = Paths::sharedInstance()->loadConfigures($configures);

        // 検証
        $this->assertSame($configures['default']['paths']['cache'], $paths->cache);
        $this->assertSame($configures['default']['paths']['compile'], $paths->compile);
        $this->assertSame($configures['default']['paths']['template'], $paths->template);
        $this->assertSame($configures['default']['paths']['javascript'], $paths->javascript);
        $this->assertSame($configures['default']['paths']['javascript_library'], $paths->javascript_library);
        $this->assertSame($configures['default']['paths']['stylesheet'], $paths->stylesheet);
        $this->assertSame($configures['default']['paths']['stylesheet_library'], $paths->stylesheet_library);
        $this->assertSame($configures['default']['paths']['smartyplugin'], $paths->smartyplugin);
    }
}
