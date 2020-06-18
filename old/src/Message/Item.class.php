<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Message;

use Citrus\Citrus;

/**
 * メッセージアイテム
 */
class Item
{
    /** @var string message type */
    const TYPE_MESSAGE = 'message';

    /** @var string message type */
    const TYPE_SUCCESS = 'success';

    /** @var string message type */
    const TYPE_WARNING = 'warning';

    /* @var string* message type */
    const TYPE_ERROR = 'error';

    /** @var string message type */
    const TAG_COMMON = 'common';

    /** @var string date */
    public $date;

    /**  @var string message type */
    public $type;

    /**  @var string message name */
    public $name;

    /**  @var string message description */
    public $description;

    /**  @var string message tag */
    public $tag;



    /**
     * constructor
     *
     * @param string|string[] $description
     * @param string|null     $type
     * @param string|null     $name
     * @param string|null     $date
     * @param string|null     $tag
     */
    public function __construct($description = null, $type = null, $name = null, $date = null, $tag = null)
    {
        // 内容がなければ無効
        if (true === is_null($description))
        {
            return;
        }

        // 日時
        $date = ($date ?: Citrus::$TIMESTAMP_FORMAT);

        // タイプ
        $type = ($type ?: self::TYPE_MESSAGE);

        // タグ
        $tag = ($tag ?: self::TAG_COMMON);

        // 名称
        if (true === is_null($name))
        {
            $names = [
                self::TYPE_MESSAGE => 'メッセージ',
                self::TYPE_WARNING => '注意',
                self::TYPE_SUCCESS => '成功',
                self::TYPE_ERROR => 'エラー',
            ];
            $name = $names[$type];
        }
        if (true === is_array($description))
        {
            $result = '';
            foreach ($description as $row)
            {
                $result .= ' '.$row;
            }
            $description = $result;
        }

        $this->description = $description;
        $this->type = $type;
        $this->name = $name;
        $this->date = $date;
        $this->tag = $tag;
        $this->date = Citrus::$TIMESTAMP_FORMAT;
    }
}
