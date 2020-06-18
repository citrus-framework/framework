<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2019, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Test\Router;

use Citrus\Configure\ConfigureException;
use Citrus\Router\Device;
use Citrus\Router\Item;
use PHPUnit\Framework\TestCase;

/**
 * ルーティングアイテムのテスト
 */
class ItemTest extends TestCase
{
    /**
     * @test
     * @throws ConfigureException
     */
    public function パスを読み込んでパースできる()
    {
        // 設定値
        $configures = require(dirname(__DIR__) . '/citrus-configure.php');

        // 生成
        /** @var Device $router_device */
        $router_device = Device::sharedInstance()->loadConfigures($configures);

        // URLパス設計
        $device = 'pc';
        $document = 'user';
        $action = 'login';
        $parameters = ['email' => 'hoge@example.com'];
        $request = [
            'url' => sprintf('/%s/%s/%s', $device, $document, $action),
        ];
        $request = array_merge($request, $parameters);

        // ルーティングアイテムの生成
        $item = new Item($router_device);
        $item->parse($request['url']);
        $item->add('parameters', $parameters);

        // 検証
        $this->assertSame($item->device, $device);
        $this->assertSame($item->document, $document);
        $this->assertSame($item->action, $action);
        $this->assertSame($item->parameters, $parameters);
    }
}
