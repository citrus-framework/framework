<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Test;

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
}
