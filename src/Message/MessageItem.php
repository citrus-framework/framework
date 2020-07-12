<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Message;

use Citrus\Variable\Stockers\StockedItem;

/**
 * メッセージアイテム
 */
class MessageItem extends StockedItem
{
    /** @var string message type */
    public const TYPE_MESSAGE = 'message';

    /** @var string message type */
    public const TYPE_SUCCESS = 'success';

    /** @var string message type */
    public const TYPE_WARNING = 'warning';

    /* @var string* message type */
    public const TYPE_ERROR = 'error';
}
