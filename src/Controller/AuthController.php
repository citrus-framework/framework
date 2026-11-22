<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitronIssue All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.besidesplus.net/
 */

namespace Citrus\Controller;

use Citrus\Authentication;
use Citrus\Authentication\AuthenticationException;
use Citrus\Authentication\AuthItem;
use Citrus\Authentication\BearerToken;
use Citrus\Authentication\JWT;
use Citrus\Authentication\JWTException;
use Citrus\Contract;
use Citrus\Database\Connection\ConnectionPool;
use Citrus\Http\Server\Request;
use Citrus\Http\Server\Response;
use Citrus\Session;

/**
 * 認証処理
 */
class AuthController extends ApiController
{
    /**
     * サインイン
     * @param Request $request
     * @return Response
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function signIn(#[\SensitiveParameter] Request $request): Response
    {
        /** @var AuthItem $user */
        $user = Contract::sharedInstance()->autoParse();
        // 認証処理
        $is_authenticated = true;
        try
        {
            $is_authenticated = Authentication::sharedInstance()->isAuthenticated($user);
        }
        catch (AuthenticationException $e)
        {
            $is_authenticated = Authentication::sharedInstance()->authorize($user);
        }

        // 認証失敗
        if ($is_authenticated === false)
        {
            header('HTTP/1.0 401 Unauthorized');
            exit;
        }

        /** @var AuthItem $item 成功したらトークン取得 */
        $item = Session::$session->call(Authentication::SESSION_KEY);
        return AuthResponse::withToken($item->token);
    }

    /**
     * ユーザー情報
     * @param Request $request
     * @return Response
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function user(#[\SensitiveParameter] Request $request): Response
    {
        $item = $this->callBearerItem();
        // DBのトークンと一致しなければ認証失敗
        AuthenticationException::exceptionElse(
            Authentication::sharedInstance()->isAuthenticated($item),
            '認証できませんでした',
        );

        /** @var AuthItem $item 成功したらトークン取得 */
        $item = Session::$session->call(Authentication::SESSION_KEY);
        $item->remove([
            'password',
        ]);
        return AuthResponse::withItem($item);
    }

    /**
     * 認証チェック
     */
    public function verify(): void
    {
        try
        {
            // Bearer 文字列の署名・期限と、DBのトークンとの一致を確かめる
            Authentication::sharedInstance()->verifyToken(BearerToken::fromHeaders(getallheaders()));
        }
        catch (JWTException|AuthenticationException $e)
        {
            // 有効期限切れ・トークン不一致などはException
            header('HTTP/1.0 401 Unauthorized');
            exit;
        }
    }

    /**
     * Bearer のトークンから認証アイテムを作る
     * @return AuthItem
     * @throws JWTException
     */
    private function callBearerItem(): AuthItem
    {
        // Bearer 文字列の取得
        $authorization = BearerToken::fromHeaders(getallheaders());
        $payload = [];
        if ($authorization !== '')
        {
            $payload = (new JWT(ConnectionPool::callDefault()))->decode($authorization);
        }

        $item = new AuthItem();
        $item->user_id = $payload['user_id'] ?? null;
        $item->expired_at = date('Y-m-d H:i:s', $payload['exp'] ?? null);
        $item->token = $authorization;
        return $item;
    }
}
