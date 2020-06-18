<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Library;

use Citrus\Configure\Paths;

/**
 * Smarty3ラッパー
 */
class Smarty3 extends \Smarty
{
    /**
     * constructor
     */
    public function __construct()
    {
        parent::__construct();
        $paths = Paths::sharedInstance();
        $this->setTemplateDir($paths->callTemplate());
        $this->setCompileDir($paths->callCompile());
        $this->setCacheDir($paths->callCache());
        $this->caching = 0;
        $this->addPluginsDir([dirname(__FILE__) . '/smarty3_plugins']);
    }
}
