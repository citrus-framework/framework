<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitronIssue All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.besidesplus.net/
 */

namespace Citrus\Controller;

use Citrus\Authentication;
use Citrus\Authentication\AuthItem;
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
     *
     * @param Request $request
     * @return Response
     */
    public function signin(Request $request): Response
    {
        /** @var AuthItem $user */
        $user = Contract::sharedInstance()->autoParse();
        // 認証処理
        $is_authenticated = (new JWT(ConnectionPool::callDefault()))->authorize($user);

        // 認証失敗
        if (false === $is_authenticated)
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
     *
     * @param Request $request
     * @return Response
     */
    public function user(Request $request): Response
    {
        $jwt = new JWT(ConnectionPool::callDefault());

        // Bearer 文字列の取得
        $headers = getallheaders();
        $authorization = explode(' ', $headers['Authorization'])[1];
        $payload = $jwt->decode($authorization);

        $item = new AuthItem();
        $item->user_id = $payload['user_id'];
        $item->expired_at = date('Y-m-d H:i:s', $payload['exp']);
        $item->token = $authorization;
        $jwt->isAuthenticated($item);

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
            // Bearer 文字列の取得
            $headers = getallheaders();
            $authorization = explode(' ', $headers['Authorization'])[1];
            // decodeすることでExceptionチェックする
            (new JWT(ConnectionPool::callDefault()))->decode($authorization);
        }
        catch (JWTException $e)
        {
            // 有効期限が切れたらException
            header('HTTP/1.0 401 Unauthorized');
            exit;
        }
    }
}
