<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Authentication;

use Citrus\Database\Columns;

/**
 * 認証アイテム
 */
class AuthItem extends Columns
{
    /** @var string user id */
    public $user_id;

    /** @var string|null password */
    public $password;

    /** @var string token */
    public $token;

    /** @var string expired at */
    public $expired_at;
}
