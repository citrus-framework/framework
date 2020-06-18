<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2019, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Test;

use Citrus\Configure\ConfigureException;
use Citrus\Router;
use Citrus\Router\Rule;
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
    public function 設定を読み込んで適用できる()
    {
        // 設定値
        $configures = require(dirname(__DIR__) . '/tests/citrus-configure.php');

        // 生成
        /** @var Rule $rule */
        $rule = Rule::sharedInstance()->loadConfigures($configures);

        // 検証
        $this->assertSame($configures['default']['rule']['default'], $rule->default);
        $this->assertSame($configures['default']['rule']['login'], $rule->login);
        $this->assertSame($configures['default']['rule']['error404'], $rule->error404);
        $this->assertSame($configures['default']['rule']['error503'], $rule->error503);
    }



    /**
     * @test
     * @throws ConfigureException
     */
    public function ルーティングアイテムを生成できる()
    {
        // 設定値
        $configures = require(dirname(__DIR__) . '/tests/citrus-configure.php');

        // 生成
        /** @var Router $router */
        $router = Router::sharedInstance()->loadConfigures($configures);

        // URLパス設計
        $device = 'pc';
        $document = 'user';
        $action = 'login';
        $parameters = ['email' => 'hoge@example.com'];
        $request = [
            'url' => sprintf('/%s/%s/%s', $device, $document, $action),
        ];
        $request = array_merge($request, $parameters);

        // アイテムの生成
        $item = $router->factory($request);

        // 検証
        $this->assertSame($item->device, $device);
        $this->assertSame($item->document, $document);
        $this->assertSame($item->action, $action);
        $this->assertSame($item->parameters, $parameters);
    }
}
