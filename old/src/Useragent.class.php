<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus;

use Citrus\Router\Device;
use Citrus\Useragent\Carrier;
use Citrus\Useragent\Element;

/**
 * ユーザーエージェント処理
 */
class Useragent
{
    /** ユーザーエージェントパターン配列のpregパターン */
    const PATTERN_KEY = 0;

    /** ユーザーエージェントパターン配列のpreg一致時のデバイス */
    const PATTERN_DEVICE = 1;

    /** ユーザーエージェントパターン配列のpreg一致時のキャリア */
    const PATTERN_CARRIER = 2;



    /** @var bool 初期化済みフラグ */
    public static $IS_INITIALIZED = false;



    /**
     * call useragent
     *
     * @param string|null $useragent
     * @return Element
     */
    public static function callUseragent(string $useragent = null): Element
    {
        return self::vague($useragent);
    }



    /**
     * useragent vague
     * ユーザエージェントについて曖昧な情報を返す
     *
     * @param string|null $useragent
     * @return Element
     */
    public static function vague(string $useragent = null): Element
    {
        // 指定が無い場合はデフォルト値
        if (true === is_null($useragent))
        {
            $useragent = trim($_SERVER['HTTP_USER_AGENT'] ?? '');
        }

        // element
        $element = new Element();
        $element->useragent = $useragent;

        // パターンチェック
        $is_match = false;
        $patterns = self::callPatterns();
        foreach ($patterns as $pattern)
        {
            if (1 === preg_match($pattern[self::PATTERN_KEY], $useragent))
            {
                $element->device = $pattern[self::PATTERN_DEVICE];
                $element->carrier = $pattern[self::PATTERN_CARRIER];
                $is_match = true;
                break;
            }
        }
        // パターン一致しなかった場合
        if ($is_match === false)
        {
            $element->device = Device::PC;
            $element->carrier = Carrier::OTHER;
        }

        return $element;
    }



    /**
     * ユーザーエージェントパターン配列
     *
     * @return array
     */
    private static function callPatterns()
    {
        $patterns = [];

        // Mac, Win ... PC
        $patterns[] = ['/(Macintosh|Windows)/',     Device::PC,          Carrier::OTHER];
        // docomo mobile
        $patterns[] = ['/DoCoMo/',                  Device::MOBILE,      Carrier::DOCOMO];
        // au mobile
        $patterns[] = ['/KDDI.*UP.Browser/',        Device::MOBILE,      Carrier::AU];
        // softbank mobile
        $patterns[] = ['/SoftBank.*NetFront/',      Device::MOBILE,      Carrier::SOFTBANK];
        //docomo Xperia
        $patterns[] = ['/Linux;.*Android.*c100/',   Device::ANDROID,     Carrier::DOCOMO];
        // softbank iPhone
        $patterns[] = ['/iPhone;/',                 Device::IPHONE,      Carrier::SOFTBANK];
        // apple iPhone simulator
        $patterns[] = ['/iPhone Simulator;/',       Device::SMARTPHONE,  Carrier::SOFTBANK];
        // other iPad
        $patterns[] = ['/iPad;/',                   Device::IPAD,        Carrier::OTHER];
        // other iPod
        $patterns[] = ['/iPod;/',                   Device::SMARTPHONE,  Carrier::OTHER];
        // android
        $patterns[] = ['/Linux;.*Android/',         Device::ANDROID,     Carrier::OTHER];
        // android
        $patterns[] = ['/Android/',                 Device::ANDROID,     Carrier::OTHER];
        // bot
        $patterns[] = ['/(Googlebot|Baiduspider)/', Device::PC,          Carrier::OTHER];

        return $patterns;
    }
}
