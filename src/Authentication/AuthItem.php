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

    /** @var string user id */
    public string $user_id;

    /** @var string|null password */
    public string|null $password;

    /** @var string token */
    public string $token;

    /** @var string expired at */
    public string $expired_at;
}
