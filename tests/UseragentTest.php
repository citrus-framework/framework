<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Test;

use Citrus\Useragent;
use PHPUnit\Framework\TestCase;

/**
 * ユーザーエージェント処理のテスト
 */
class UseragentTest extends TestCase
{
    /**
     * @test
     */
    public function isMobile_想定通り()
    {
        // iPhone
        $useragent = 'Mozilla/5.0 (iPhone; CPU iPhone OS 10_2 like Mac OS X) AppleWebKit/602.3.12 (KHTML, like Gecko) Version/10.0 Mobile/14C92 Safari/602.1';
        $this->assertTrue(Useragent::isMobile($useragent));

        // iPad
        $useragent = 'Mozilla/5.0 (iPad; CPU OS 10_2 like Mac OS X) AppleWebKit/602.3.12 (KHTML, like Gecko) Version/10.0 Mobile/14C92 Safari/602.1';
        $this->assertFalse(Useragent::isMobile($useragent));

        // Android
        $useragent = 'Mozilla/5.0 (Linux; Android 7.1.1; Nexus 5X Build/N4F26I) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.91 Mobile Safari/537.36';
        $this->assertTrue(Useragent::isMobile($useragent));

        // Windows
        $useragent = 'Google Chrome 32bit Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36';
        $this->assertFalse(Useragent::isMobile($useragent));

        // Mac
        $useragent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:50.0) Gecko/20100101 Firefox/50.0';
        $this->assertFalse(Useragent::isMobile($useragent));
    }
}
