<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Test;

use Citrus\Configure\ConfigureException;
use Citrus\Http\Server\Request;
use Citrus\Router;
use PHPUnit\Framework\TestCase;

/**
 * ルーター処理のテスト
 */
class RouterTest extends TestCase
{
    /**
     * @test
     * @throws ConfigureException
     */
    public function loadConfigures_想定通り()
    {
        // 設定値
        $configures = require(dirname(__DIR__) . '/tests/citrus-configure.php');

        // 生成
        $router = Router::sharedInstance()->loadConfigures($configures);

        // 検証
        $this->assertSame($configures['default']['router']['default_url'], $router->configures['default_url']);
    }

    /**
     * @test
     * @throws ConfigureException
     */
    public function factory_想定通り()
    {
        // 設定値
        $configures = require(dirname(__DIR__) . '/tests/citrus-configure.php');

        // 生成
        $router = Router::sharedInstance()->loadConfigures($configures);

        // URLパス設計
        $_SERVER['REQUEST_URI'] = '/api/user/auth/login?callbackUrl=http://localhost:3333/';
        $_SERVER['REQUEST_METHOD'] = 'get';
        $_GET = [
            'callbackUrl' => 'http://localhost:3333/',
        ];

        // 想定
        $protocol = 'api';
        $documents = [
            'user',
            'auth',
        ];
        $action = 'login';
        $parameters = [
            'callbackUrl' => 'http://localhost:3333/'
        ];

        // アイテムの生成
        $router->factory(Request::generate());

        // 検証
        $this->assertSame($router->protocol->value, $protocol);
        $this->assertSame($router->documents, $documents);
        $this->assertSame($router->action, $action);
        $this->assertSame($router->parameters, $parameters);
    }
}
