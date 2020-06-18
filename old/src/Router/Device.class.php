<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Router;

use Citrus\Configure\Configurable;
use Citrus\Variable\Singleton;

/**
 * ルーティングデバイス設定
 */
class Device extends Configurable
{
    use Singleton;

    /** @var string */
    const DEFAULT = 'default';

    /** @var string */
    const XHR = 'xhr';

    /** @var string */
    const PC = 'pc';

    /** @var string */
    const MOBILE = 'mobile';

    /** @var string */
    const IPHONE = 'iphone';

    /** @var string */
    const IPAD = 'ipad';

    /** @var string */
    const ANDROID = 'android';

    /** @var string */
    const SMARTPHONE = 'smartphone';

    /** @var string */
    const TABLET = 'tablet';

    /** @var string */
    const ROBOT = 'robot';

    /** @var string */
    const SIMULATOR = 'simulator';

    /** @var string */
    const OTHER = 'other';

    /** @var array デバイスルーティング */
    public $device_routes = [];



    /**
     * {@inheritDoc}
     */
    public function loadConfigures(array $configures = []): Configurable
    {
        // 設定配列の読み込み
        parent::loadConfigures($configures);

        // デバイス一覧
        $device_list = Device::deviceList();

        // デバイス設定
        $configure_devices = $this->configures;

        // デバイスルーティング設定
        foreach ($device_list as $one)
        {
            $this->device_routes[$one] = ($configure_devices[$one] ?? null);
        }

        return $this;
    }



    /**
     * call device list
     *
     * @return array
     */
    public static function deviceList(): array
    {
        return [
            self::DEFAULT,
            self::XHR,
            self::PC,
            self::MOBILE,
            self::IPHONE,
            self::IPAD,
            self::ANDROID,
            self::SMARTPHONE,
            self::ROBOT,
            self::SIMULATOR,
            self::OTHER,
        ];
    }



    /**
     * {@inheritDoc}
     */
    protected function configureKey(): string
    {
        return 'device';
    }



    /**
     * {@inheritDoc}
     */
    protected function configureDefaults(): array
    {
        return [
            'default' => 'pc',
        ];
    }



    /**
     * {@inheritDoc}
     */
    protected function configureRequires(): array
    {
        return [
            'default',
        ];
    }
}
