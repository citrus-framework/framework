#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

date_default_timezone_set('Asia/Tokyo');

require_once '../vendor/autoload.php';

use Citrus\Authentication;
use Citrus\Configure\Application;
use Citrus\Database\Connection\ConnectionPool;
use Citrus\Database\DSN;
use Citrus\Gateway;
use Citrus\Logger;
use Citrus\Router;

$configure_path = dirname(__FILE__).'/citrus-configure.php';
$configures = include($configure_path);
Application::sharedInstance()->loadConfigures($configures);
Authentication::sharedInstance()->loadConfigures($configures);
Logger::sharedInstance()->loadConfigures($configures);
Router::sharedInstance()->loadConfigures($configures);

// コネクションプール
ConnectionPool::callConnection(DSN::getInstance()->loadConfigures($configures), true);

Gateway::main(Gateway::TYPE_COMMAND, $configures);
