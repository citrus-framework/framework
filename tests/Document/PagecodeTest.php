<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Test\Document;

use Citrus\Configure\ConfigureException;
use Citrus\Document\Pagecode;
use Citrus\Formmap;
use Citrus\Router;
use Citrus\Variable\ReflectionProperties;
use PHPUnit\Framework\TestCase;
use Test\Sample\Controller\Pc\HomeController;

/**
 * ページコード処理のテスト
 */
class PagecodeTest extends TestCase
{
    /** @var Pagecode */
    private $pagecode;

    /**
     * {@inheritDoc}
     * @throws ConfigureException
     */
    public function setUp(): void
    {
        parent::setUp();

        // コントローラーのセットアップ
        $configures = require(dirname(__DIR__) . '/citrus-configure.php');

        // GETリクエストに固定する
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/home';

        Formmap::sharedInstance()->loadConfigures($configures);
        Router::sharedInstance()->loadConfigures($configures);

        $controller = new HomeController();
        $controller->run();
        $this->pagecode = ReflectionProperties::call($controller, 'pagecode');
    }



    /**
     * @test
     */
    public function リソースパス設定_Javascript()
    {
        // セットアップ済みページコードの取得
        $pagecode = $this->pagecode;

        // 読み込みたいJavascript
        $resources = [
            '/Part/Pc/dummy.js',            // 直接指定
            '/cf-lib/library.js',           // ライブラリ指定
            'https://example.com/dummy.js', // 外部リソース
        ];
        // 読み込みが期待されるJavascript
        $expected_resources = [
            '/Javascript/hoge.example.com/Part/Pc/dummy.js',// 直接指定
            '/Javascript/Library/cf-lib/library.js',        // ライブラリ指定
            '/Javascript/hoge.example.com/Page/Pc/Home.js', // 自動読み込み指定
            'https://example.com/dummy.js',                 // 外部リソース
        ];

        // 読み込み処理を通すと、自動読み込みも読み込まれる
        $pagecode->addJavascript($resources);

        // 検算
        $loaded_resources = $pagecode->javascripts;
        foreach ($expected_resources as $expected_resource)
        {
            $this->assertTrue(in_array($expected_resource, $loaded_resources, true));
        }
    }



    /**
     * @test
     */
    public function リソースパス設定_Styleseet()
    {
        // セットアップ済みページコードの取得
        $pagecode = $this->pagecode;

        // 読み込みたいStylesheet
        $resources = [
            '/Part/Pc/dummy.css', // 直接指定
            '/cf-lib/library.css', // ライブラリ指定
            '/Javascript/Library/cf-lib/library.css', // JSライブラリ内のCSS指定
        ];
        // 読み込みが期待されるStylesheet
        $expected_resources = [
            '/Stylesheet/hoge.example.com/Part/Pc/dummy.css',   // 直接指定
            '/Stylesheet/Library/cf-lib/library.css',           // ライブラリ指定
            '/Stylesheet/hoge.example.com/Page/Pc/Home.css',    // 自動読み込み指定
            '/Javascript/Library/cf-lib/library.css',           // JSライブラリ内のCSS指定
        ];

        // 読み込み処理を通すと、自動読み込みも読み込まれる
        $pagecode->addStylesheet($resources);

        // 検算
        $loaded_resources = $pagecode->stylesheets;
        foreach ($expected_resources as $expected_resource)
        {
            $this->assertTrue(in_array($expected_resource, $loaded_resources, true));
        }
    }
}
