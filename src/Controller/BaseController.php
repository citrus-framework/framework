<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Controller;

use Citrus\Router;

/**
 * コントローラー共通処理
 */
abstract class BaseController
{
    /** @var Router */
    protected Router $router;



    /**
     * Router取得
     *
     * @return Router
     */
    protected function callRouter(): Router
    {
        $this->router ??= Router::sharedInstance()->factory();
        return $this->router;
    }
}
