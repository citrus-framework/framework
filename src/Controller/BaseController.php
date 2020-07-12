<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Controller;

use Citrus\Formmap;
use Citrus\Router;

/**
 * コントローラー共通処理
 */
abstract class BaseController
{
    /** @var Formmap */
    protected $formmap;

    /** @var Router */
    protected $router;



    /**
     * Formmap取得
     *
     * @return Formmap
     */
    protected function callFormmap(): Formmap
    {
        $this->formmap = ($this->formmap ?: Formmap::sharedInstance());
        return $this->formmap;
    }



    /**
     * Router取得
     *
     * @return Router
     */
    protected function callRouter(): Router
    {
        $this->router = ($this->router ?: Router::sharedInstance()->factory());
        return $this->router;
    }
}
