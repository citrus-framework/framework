<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Integration\Dmm;

/**
 * サービス情報
 */
class Service
{
    /** @var string service dmm.com AKB48グループ, 月額動画 */
    const COM_MONTHLY = 'monthly';

    /** @var string service dmm.com 動画 */
    const COM_DIGITAL = 'digital';

    /** @var string service dmm.com 電子書籍 */
    const COM_EBOOK = 'ebook';

    /** @var string service dmm.com PCソフト */
    const COM_PCSOFT = 'pcsoft';

    /** @var string service dmm.com 通販 */
    const COM_MONO = 'mono';

    /** @var string service dmm.com いろいろレンタル */
    const COM_RENTAL = 'rental';

    /** @var string service dmm.com 通販 */
    const COM_NANDEMO = 'nandemo';

    /** @var string service dmm.r18 動画 */
    const R18_DIGITAL = 'digital';

    /** @var string service dmm.r18 月額動画 */
    const R18_MONTHLY = 'monthly';

    /** @var string service dmm.r18 10円動画 */
    const R18_PPM = 'ppm';

    /** @var string service dmm.r18 DVDレンタル */
    const R18_RENTAL = 'rental';

    /** @var string service dmm.r18 通販 */
    const R18_MONO = 'mono';

    /** @var string service dmm.r18 美少女ゲーム */
    const R18_PCGAME = 'pcgame';

    /** @var string service dmm.r18 同人 */
    const R18_DOUJIN = 'doujin';

    /** @var string service dmm.r18 電子書籍 */
    const R18_EBOOK = 'ebook';

    /** @var array service dmm.r18の一覧 */
    public static $SERVICE_R18_NAMES = [
        self::R18_DIGITAL => '動画',
        self::R18_MONTHLY => '月額動画',
        self::R18_PPM => '10円動画',
        self::R18_RENTAL => 'DVDレンタル',
        self::R18_MONO => '通販',
        self::R18_PCGAME => '美少女ゲーム',
        self::R18_DOUJIN => '同人',
        self::R18_EBOOK => '電子書籍',
    ];
}
