<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2019, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Test\Router;

use Citrus\Configure\ConfigureException;
use Citrus\Router\Rule;
use PHPUnit\Framework\TestCase;

/**
 * ルーティング処理のテスト
 */
class RuleTest extends TestCase
{
    /**
     * @test
     * @throws ConfigureException
     */
    public function 設定を読み込んで適用できる()
    {
        // 設定値
        $configures = require(dirname(__DIR__) . '/citrus-configure.php');

        // 生成
        /** @var Rule $routing */
        $routing = Rule::sharedInstance()->loadConfigures($configures);

        // 検証
        $this->assertSame($configures['default']['rule']['default'], $routing->default);
        $this->assertSame($configures['default']['rule']['login'], $routing->login);
        $this->assertSame($configures['default']['rule']['error404'], $routing->error404);
        $this->assertSame($configures['default']['rule']['error503'], $routing->error503);
    }
}
