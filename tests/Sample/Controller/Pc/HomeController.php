<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2019, Citrus All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.besidesplus.net/
 */

namespace Test\Sample\Controller\Pc;

use Citrus\Controller\WebController;
use Citrus\Router;

class HomeController extends WebController
{
    /**
     * @return Router|null
     */
    public function initialize(): ?Router
    {
        return parent::initialize();
    }



    /**
     * index
     *
     * @return Router|null
     */
    public function index(): ?Router
    {
        return $this->router;
    }
}
