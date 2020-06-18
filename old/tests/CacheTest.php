<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2019, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Test\Integration;

use Citrus\Cache;
use Citrus\Configure\ConfigureException;
use PHPUnit\Framework\TestCase;

/**
 * キャッシュ処理のテスト
 */
class CacheTest extends TestCase
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
        Cache::sharedInstance()->loadConfigures($configures);
    }
}
