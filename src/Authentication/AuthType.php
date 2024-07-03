<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Authentication;

/**
 * 認証タイプ
 */
enum AuthType : string
{
    /** データベース認証 */
    case DATABASE = 'database';
}
