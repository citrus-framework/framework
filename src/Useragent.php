<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus;

/**
 * ユーザーエージェント処理
 * ガラケーは対象外
 */
class Useragent
{
    /** @var string スマートフォン用の一致パターン、iPodなども含むためMOBILE */
    public const MOBILE_PATTERN = '/iPhone|iPod|Android/i';



    /**
     * モバイル端末判定
     *
     * @param string|null $useragent ユーザーエージェント文字列
     * @return bool true:モバイル端末として判定される
     */
    public static function isMobile(string $useragent = null): bool
    {
        $useragent = ($useragent ?? $_SERVER['HTTP_USER_AGENT'] ?? '');
        return (0 !== preg_match(self::MOBILE_PATTERN, $useragent));
    }
}
