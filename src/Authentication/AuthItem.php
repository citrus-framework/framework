<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Authentication;

use Citrus\Database\Columns;
use Citrus\Database\ResultSet\BindColumn;
use Citrus\Database\ResultSet\ResultClass;

/**
 * 認証アイテム
 */
class AuthItem extends Columns implements ResultClass
{
    use BindColumn;

    /** @var string|null user id */
    public string|null $user_id = null;

    /** @var string|null password */
    public string|null $password = null;

    /** @var string|null token */
    public string|null $token = null;

    /** @var string|null expired at */
    public string|null $expired_at = null;
}
