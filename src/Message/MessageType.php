<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Message;

use Citrus\Variable\Stockers\StockedType;

/**
 * メッセージアイテムタイプ
 */
enum MessageType: string implements StockedType
{
    /** message type */
    case MESSAGE = 'message';

    /** message type */
    case SUCCESS = 'success';

    /** message type */
    case WARNING = 'warning';

    /** message type */
    case ERROR = 'error';
}
