<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Integration\Dmm;

/**
 * ジャンル
 */
class Floor
{
    /** @var string */
    const ANIME = 'anime';

    /** @var string */
    const BOOK = 'book';

    /** @var string */
    const COMIC = 'comic';

    /** @var string */
    const DIGITAL_DOUJIN = 'digital_doujin';

    /** @var string */
    const DIGITAL_PCGAME = 'digital_pcgame';

    /** @var string */
    const DVD = 'dvd';

    /** @var string */
    const NIKKATSU = 'nikkatsu';

    /** @var string */
    const PCGAME = 'pcgame';

    /** @var string */
    const RENTAL_DVD = 'rental_dvd';

    /** @var string */
    const VIDEOA = 'videoa';

    /** @var string */
    const VIDEOC = 'videoc';

    /** @var array floor name list */
    public static $FLOOR_NAMES = [
        self::ANIME => 'アニメ動画',
        self::BOOK => '本',
        self::COMIC => 'コミック',
        self::DIGITAL_DOUJIN => '同人',
        self::DIGITAL_PCGAME => '美少女ゲーム',
        self::DVD => 'DVD',
        self::NIKKATSU => '成人映画',
        self::PCGAME => 'PCゲーム',
        self::RENTAL_DVD => '月額レンタル',
        self::VIDEOA => 'ビデオ',
        self::VIDEOC => '素人',
    ];
}
