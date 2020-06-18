<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Integration\Dmm;

/**
 * DMM検索条件
 */
class Condition
{
    /** @var string site dmm.com */
    const SITE_DMM_COM = 'DMM.com';

    /** @var string site dmm.r18 */
    const SITE_DMM_R18 = 'DMM.R18';

    /** @var string sort 商品 人気 */
    const SORT_ITEM_RANK = 'rank';

    /** @var string sort 商品 価格が高い順 */
    const SORT_ITEM_PRICE_DESC = 'price';

    /** @var string sort 商品 価格が安い順 */
    const SORT_ITEM_PRICE_ASC = '-price';

    /** @var string sort 商品 新着 */
    const SORT_ITEM_DATE = 'date';

    /** @var string sort 商品 評価 */
    const SORT_ITEM_REVIEW = 'review';

    /** @var string sort 女優 名前 */
    const SORT_ACTORERSS_NAME_ASC = 'name';

    /** @var string sort 女優 名前 */
    const SORT_ACTORERSS_NAME_DESC = '-name';

    /** @var string sort 女優 バスト */
    const SORT_ACTORERSS_BUST_ASC = 'bust';

    /** @var string sort 女優 バスト */
    const SORT_ACTORERSS_BUST_DESC = '-bust';

    /** @var string sort 女優 ウエスト */
    const SORT_ACTORERSS_WAIST_ASC = 'waist';

    /** @var string sort 女優 ウエスト */
    const SORT_ACTORERSS_WAIST_DESC = '-waist';

    /** @var string sort 女優 ヒップ */
    const SORT_ACTORERSS_HIP_ASC = 'hip';

    /** @var string sort 女優 ヒップ */
    const SORT_ACTORERSS_HIP_DESC = '-hip';

    /** @var string sort 女優 身長 */
    const SORT_ACTORERSS_HEIGHT_ASC = 'height';

    /** @var string sort 女優 身長 */
    const SORT_ACTORERSS_HEIGHT_DESC = '-height';

    /** @var string sort 女優 生年月日 */
    const SORT_ACTORERSS_BIRTHDAY_ASC = 'birthday';

    /** @var string sort 女優 生年月日 */
    const SORT_ACTORERSS_BIRTHDAY_DESC = '-birthday';

    /** @var string sort 女優 女優ID */
    const SORT_ACTORERSS_ID_ASC = 'id';

    /** @var string sort 女優 女優ID */
    const SORT_ACTORERSS_ID_DESC = '-id';

    /** @var string site */
    public $site = self::SITE_DMM_R18;

    /** @var string service */
    public $service = null;

    /** @var string floor 使うのはしんどい */
    public $floor = null;

    /** @var int hits */
    public $hits = 100;

    /** @var int offset */
    public $offset = 1;

    /** @var string keyword */
    public $keyword = null;

    /** @var string sort */
    public $sort = null;

    /** @var string 絞り込み TODO: 後で */
    public $article = null;

    /** @var string 絞り込み TODO: 後で */
    public $article_id = null;

    /** @var string 女優指定 */
    public $actress_id = null;
}
