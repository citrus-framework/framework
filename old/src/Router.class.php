<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus;

use Citrus\Configure\Configurable;
use Citrus\Router\Device;
use Citrus\Router\Item;
use Citrus\Router\Rule;
use Citrus\Variable\Singleton;
use Citrus\Variable\Structs;

/**
 * ドキュメントのルーティング処理
 */
class Router extends Configurable
{
    use Singleton;
    use Structs;

    /** @var Device デバイス */
    public $device;

    /** @var Rule ルール */
    public $rule;



    /**
     * {@inheritDoc}
     */
    public function loadConfigures(array $configures = []): Configurable
    {
        // 設定配列の読み込み
        parent::loadConfigures($configures);

        // デバイス
        $this->device = Device::sharedInstance()->loadConfigures($configures);

        // アクセス設定
        $this->rule = Rule::sharedInstance()->loadConfigures($configures);

        return $this;
    }



    /**
     * call default URL
     *
     * @return string
     */
    public function callDefaultURL(): string
    {
        return $this->rule->default;
    }



    /**
     * call login URL
     *
     * @return string
     */
    public function callLoginURL(): string
    {
        return $this->rule->login;
    }



    /**
     * factory
     *
     * @param array|null $request
     * @return Item
     */
    public function factory(array $request = null): Item
    {
        // デフォルトはログイン
        $request['url'] = ($request['url'] ?? self::callDefaultURL());

        // ルーティングアイテムの生成
        $item = new Item($this->device);
        $item->parse($request['url']);

        // パラメータ
        $parameters = Collection::stream($request)->filter(function ($vl, $ky) {
            return ('url' !== $ky);
        })->toList();
        $item->add('parameters', $parameters);

        return $item;
    }



    /**
     * redirect URL
     *
     * @param string|null $url
     */
    public static function redirectURL(string $url = null)
    {
        if (false === is_null($url))
        {
            header('Location: ' . $url);
            exit;
        }
    }



    /**
     * {@inheritDoc}
     */
    protected function configureKey(): string
    {
        return '';
    }



    /**
     * {@inheritDoc}
     */
    protected function configureDefaults(): array
    {
        return [];
    }



    /**
     * {@inheritDoc}
     */
    protected function configureRequires(): array
    {
        return [];
    }
}
