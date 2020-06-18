<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Router;

use Citrus\Collection;
use Citrus\Useragent;
use Citrus\Variable\Structs;

/**
 * ルーティングアイテム設定
 */
class Item
{
    use Structs;

    /** @var string */
    public $device;

    /** @var string */
    public $document;

    /** @var string */
    public $action;

    /** @var array */
    public $parameters;

    /** @var Device */
    private $router_device;



    /**
     * constructor.
     *
     * @param Device $device
     */
    public function __construct(Device $device)
    {
        $this->router_device = $device;
    }



    /**
     * url parse
     *
     * @param string|null $url
     * @return self
     */
    public function parse(string $url = null): self
    {
        // 分割
        $parts = explode('/', $url);

        // /で始まっている場合、
        // /で終わっている場合を考慮
        $parts = Collection::stream($parts)->filter(function ($vl) {
            // 空の要素を排除
            return ('' !== $vl);
        })->toValues();

        // prefix が device 設定にある場合
        if (true === in_array($parts[0], $this->router_device->device_routes))
        {
            $this->device = array_shift($parts);
        }
        // useragent から device 設定を取得
        else
        {
            $useragent = Useragent::vague();
            $this->device = $this->router_device->device_routes[$useragent->device];
        }

        // ルーティング要素が１つしか無い場合はデフォルトでindexをつける
        if (1 === count($parts))
        {
            $parts[] = 'index';
        }

        // 最終要素がactionになる
        $this->action = array_pop($parts);

        // 残った要素がdocumentになる
        $this->document = implode('-', $parts);

        return $this;
    }
}
