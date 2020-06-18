<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Authentication;

use Citrus\Database\Column;

/**
 * 認証アイテム
 */
class Item extends Column
{
    /** @var string user id */
    public $user_id;

    /** @var string|null password */
    public $password;

    /** @var string token */
    public $token;

    /** @var string keep at */
    public $keep_at;
}
