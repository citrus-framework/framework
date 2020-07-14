<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Controller;

use Citrus\Document\Pager;
use Citrus\Http\Server\Response;

/**
 * WEB系処理のためのレスポンス
 */
class WebResponse extends Response
{
    /** @var Pager */
    public $pager;
}
