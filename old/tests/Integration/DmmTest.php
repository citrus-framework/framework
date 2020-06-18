<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2019, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Test\Integration;

use Citrus\Configure\ConfigureException;
use Citrus\Integration\Dmm;
use PHPUnit\Framework\TestCase;

/**
 * DMM処理のテスト
 */
class DmmTest extends TestCase
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
        /** @var Dmm $dmm */
        $dmm = Dmm::sharedInstance()->loadConfigures($configures);

        // 検証
        $this->assertSame($configures['default']['dmm']['api_id'], $dmm->configures['api_id']);
        $this->assertSame($configures['default']['dmm']['affiliate_id'], $dmm->configures['affiliate_id']);
        $this->assertSame($configures['default']['dmm']['ssl'], $dmm->configures['ssl']);
    }
}
