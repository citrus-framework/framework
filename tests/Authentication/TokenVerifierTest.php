<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2026, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Test\Authentication;

use Citrus\Authentication\AuthenticationException;
use Citrus\Authentication\AuthItem;
use Citrus\Authentication\JWT;
use Citrus\Authentication\JWTException;
use Citrus\Authentication\TokenVerifier;
use Citrus\Authentication\UserLookup;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * トークンの検証のテスト
 */
class TokenVerifierTest extends TestCase
{
    /**
     * decode の結果を指定した TokenVerifier を作る
     * @param array|JWTException $decoded decode が返すペイロード、または投げる例外
     * @param AuthItem|null       $user    DBのユーザー
     * @return TokenVerifier
     */
    private function makeVerifier(array|JWTException $decoded, AuthItem|null $user = null): TokenVerifier
    {
        $jwt = $this->createMock(JWT::class);
        if ($decoded instanceof JWTException)
        {
            $jwt->method('decode')->willThrowException($decoded);
        }
        else
        {
            $jwt->method('decode')->willReturn($decoded);
        }

        $lookup = $this->createMock(UserLookup::class);
        $lookup->method('find')->willReturn($user);

        return new TokenVerifier($jwt, $lookup);
    }

    #[Test]
    public function verify_空のトークンはJWTException(): void
    {
        $this->expectException(JWTException::class);

        $this->makeVerifier(['user_id' => 'u1'])->verify('');
    }

    #[Test]
    public function verify_壊れたトークンはJWTException(): void
    {
        $this->expectException(JWTException::class);

        $this->makeVerifier(new JWTException('トークン要素数が不足しています'))->verify('broken');
    }

    #[Test]
    public function verify_ユーザーがいなければAuthenticationException(): void
    {
        $this->expectException(AuthenticationException::class);

        $this->makeVerifier(['user_id' => 'u1'], null)->verify('abc.def.ghi');
    }

    #[Test]
    public function verify_DBのトークンと違えばAuthenticationException(): void
    {
        $user = new AuthItem();
        $user->user_id = 'u1';
        $user->token = 'other.token.value';

        $this->expectException(AuthenticationException::class);

        $this->makeVerifier(['user_id' => 'u1'], $user)->verify('abc.def.ghi');
    }

    #[Test]
    public function verify_DBのトークンと同じなら例外にならない(): void
    {
        $user = new AuthItem();
        $user->user_id = 'u1';
        $user->token = 'abc.def.ghi';

        $this->makeVerifier(['user_id' => 'u1'], $user)->verify('abc.def.ghi');

        $this->addToAssertionCount(1);
    }
}
