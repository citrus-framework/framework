#!/usr/bin/env php
<?php
/**
 * @copyright   Copyright 2017, Citrus/besidesplus All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */
/**
 * sample :
 *
./bin/migration --directory=../medica6.project.tk --action=decoy
./bin/migration --directory=../medica6.project.tk --action=generate --name=CreateTableActors
./bin/migration --directory=../medica6.project.tk --action=up
./bin/migration --directory=../medica6.project.tk --action=up --version=20170717031426
 */
namespace Citrus\Bin;

date_default_timezone_set('Asia/Tokyo');

include_once dirname(__FILE__) . '/../Configure.class.php';

use Citrus\Configure;
use Citrus\Migration;
use Citrus\NVL;

// 実行ファイル名削除
unset($argv[0]);

// 設定パース
$settings = [];
foreach ($argv as $arg)
{
    list($ky, $vl) = explode('=', $arg);
    $settings[$ky] = $vl;
}

// 設定(ディレクトリ)
$directory = $settings['--directory'];
// 設定(操作)
$action = $settings['--action'];


// application configure
$application_directory = dirname(__FILE__) . '/../' . $directory;
if (substr($directory, 0, 1) === '/')
{
    $application_directory = $directory;
}
Configure::initialize($application_directory . '/citrus-configure.php');

$dsns = [];
foreach (Configure::$CONFIGURE_ITEMS as $one)
{
    $key = $one->database->serialize();
    $dsns[$key] = $one->database;
}


// 実行
switch ($action)
{
    // 生成処理
    case Migration::ACTION_GENERATE :
        $generate_name = $settings['--name'];
        Migration::generate($application_directory, $generate_name);
        break;
    // マイグレーションUP実行
    case Migration::ACTION_MIGRATION :
    case Migration::ACTION_MIGRATION_UP :
        $version = NVL::ArrayVL($settings, '--version', null);
        $version = NVL::coalesceNull($version, null);
        Migration::up($application_directory, $dsns, $version);
        break;
    // マイグレーションDOWN実行
    case Migration::ACTION_MIGRATION_DOWN :
        $version = NVL::ArrayVL($settings, '--version', null);
        $version = NVL::coalesceNull($version, null);
        Migration::down($application_directory, $dsns, $version);
        break;
    // マイグレーションREBIRTH実行
    case Migration::ACTION_MIGRATION_REBIRTH :
        $version = NVL::ArrayVL($settings, '--version', null);
        $version = NVL::coalesceNull($version, null);
        Migration::rebirth($application_directory, $dsns, $version);
        break;
    default:
}




