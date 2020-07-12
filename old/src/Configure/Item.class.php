<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Configure;

use Citrus\Database\DSN;
use Citrus\NVL;
use Citrus\Router\Rule;

/**
 * 設定アイテム
 */
class Item
{
    /** @var string */
    public $domain;

    /**
     * @var Application
     * @deprecated
     */
    public $application;

    /** @var DSN */
    public $database;

    /**
     * @var Paths
     * @deprecated
     */
    public $paths;

    /** @var Rule */
    public $routing;




    /**
     * constructor.
     *
     * @param array $default_configure
     * @param array $configure
     * @deprecated
     */
    public function __construct(array $default_configure, array $configure)
    {
        // application
        $key = 'application';
        $this->application = new Application();
        $this->application->bind(NVL::ArrayVL($default_configure, $key, []));
        $this->application->bind(NVL::ArrayVL($configure, $key, []));

        // database.sh
        $key = 'database';
        $this->database = new DSN();
        $this->database->bind(NVL::ArrayVL($default_configure, $key, []));
        $this->database->bind(NVL::ArrayVL($configure, $key, []));

        // paths
        $key = 'paths';
        $this->paths = new Paths();
        $this->paths->domain = $this->application->domain;
        $this->paths->bind(NVL::ArrayVL($default_configure, $key, []));
        $this->paths->bind(NVL::ArrayVL($configure, $key, []));

        // routing
        // TODO: 新しいConfigureへの頭からの修正が必要
        $key = 'routing';
        $this->routing = new Rule();
        $this->routing->bind(NVL::ArrayVL($default_configure, $key, []));
        $this->routing->bind(NVL::ArrayVL($configure, $key, []));
    }
}
