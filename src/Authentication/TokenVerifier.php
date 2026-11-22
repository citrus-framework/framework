<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2026, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Authentication;

/**
 * Bearer のトークンの検証(署名・期限と、DBのユーザーのトークンとの一致)
 */
class TokenVerifier
{
    /**
     * @param JWT        $jwt    JWT
     * @param UserLookup $lookup ユーザー取得
     */
    public function __construct(
        private JWT $jwt,
        private UserLookup $lookup,
    ) {
    }

    /**
     * トークンの署名・期限を確かめ、DBのユーザーのトークンと同じことを確かめる
     * @param string $token トークン
     * @return void
     * @throws JWTException            トークンが空、または署名・期限が有効ではない
     * @throws AuthenticationException ユーザーがいない、またはDBのトークンと違う
     */
    public function verify(string $token): void
    {
        JWTException::exceptionIf($token === '', '認証トークンがありません');

        // decodeすることで署名と期限をチェックする
        $payload = $this->jwt->decode($token);

        $user = $this->lookup->find($payload['user_id'] ?? null);
        AuthenticationException::exceptionIf(
            $user === null or $user->token !== $token,
            'トークンがユーザーの認証情報と一致しません',
        );
    }
}
