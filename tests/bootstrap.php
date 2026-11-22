<?php

use Citrus\Configure\Application;
use Citrus\Logger;

require __DIR__ . '/../vendor/autoload.php';

// ユニットテスト用
define('UNIT_TEST', true);

// 設定ファイル
$configure_path = dirname(__DIR__). '/tests/citrus-configure.php';

\Citrus\Session::factory();

// 各種初期化
$configures = include($configure_path);
Application::sharedInstance()->loadConfigures($configures);
Logger::sharedInstance()->loadConfigures($configures);