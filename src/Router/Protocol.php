<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Router;

/**
 * ルーティング処理の許可されたプロトコル
 */
enum Protocol: string
{
    /** api */
    case API = 'api';

    /** html web */
    case WEB = 'web';

    /** html web pc */
    case PC = 'pc';

    /** html web smartphone */
    case SP = 'sp';
}
