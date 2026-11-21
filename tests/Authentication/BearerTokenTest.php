<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2026, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Test\Authentication;

use Citrus\Authentication\BearerToken;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Bearer のトークンのテスト
 */
class BearerTokenTest extends TestCase
{
    #[Test]
    public function fromHeaders_ヘッダ名の大文字小文字を問わず取り出せる(): void
    {
        $this->assertSame('abc.def.ghi', BearerToken::fromHeaders(['Authorization' => 'Bearer abc.def.ghi']));
        $this->assertSame('abc.def.ghi', BearerToken::fromHeaders(['authorization' => 'Bearer abc.def.ghi']));
        $this->assertSame('abc.def.ghi', BearerToken::fromHeaders(['AUTHORIZATION' => 'Bearer abc.def.ghi']));
        $this->assertSame('abc.def.ghi', BearerToken::fromHeaders(['123' => 'value', 'Authorization' => 'Bearer abc.def.ghi']));
    }

    #[Test]
    public function fromHeaders_ヘッダが無いときは空文字(): void
    {
        $this->assertSame('', BearerToken::fromHeaders([]));
        $this->assertSame('', BearerToken::fromHeaders(['Content-Type' => 'application/json']));
        $this->assertSame('', BearerToken::fromHeaders(['123' => 'value']));
    }

    #[Test]
    public function fromHeaders_トークンが無いときは空文字(): void
    {
        $this->assertSame('', BearerToken::fromHeaders(['Authorization' => '']));
        $this->assertSame('', BearerToken::fromHeaders(['Authorization' => 'Bearer']));
        $this->assertSame('', BearerToken::fromHeaders(['Authorization' => 'Bearer ']));
    }
}
