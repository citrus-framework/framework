<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus;

use Citrus\Configure\Application;
use Citrus\Configure\ConfigureException;
use Citrus\Configure\Paths;
use Citrus\Variable\Directory;

/**
 * 設定
 */
class Configure
{
    /** @var array 設定ファイル内容 */
    public static $CONFIGURES = [];

    /** @var string */
    public static $PATH_FRAMEWORK;

    /** @var string dir */
    public static $DIR_APP;

    /** @var string dir */
    public static $DIR_INTEGRATION;

    /** @var string dir */
    public static $DIR_INTEGRATION_SQLMAP;

    /** @var bool */
    private static $IS_INITIALIZED_FRAMEWORK = false;

    /** @var bool */
    private static $IS_INITIALIZED_DIRECTORY = false;



    /**
     * configure initilize
     *
     * @param string $path_configure
     */
    public static function initialize($path_configure)
    {
        self::$CONFIGURES = include($path_configure);

        // framework initialize
        self::fremework();

        // directory initialize
        $app_path = self::$CONFIGURES['default']['application']['path'];
        self::directory($app_path);

        // configure initialize
        self::configure($path_configure);
    }



    /**
     * fremework initialize
     */
    public static function fremework()
    {
        // is initialized
        if (self::$IS_INITIALIZED_FRAMEWORK === true)
        {
            return;
        }

        // path framework
        self::$PATH_FRAMEWORK = dirname(__FILE__).'/Citrus.class.php';

        // citrus intialize
        Citrus::initialize();

        // initialized
        self::$IS_INITIALIZED_FRAMEWORK = true;
    }



    /**
     * directory initialize
     *
     * @param string $path_application_dir
     */
    public static function directory(string $path_application_dir)
    {
        // is initialized
        if (self::$IS_INITIALIZED_DIRECTORY === true)
        {
            return;
        }

        // 親参照指定を取り除く
        $path_application_dir = Directory::suitablePath($path_application_dir);

        // directory
        self::$DIR_APP                  = $path_application_dir;
        // dir integration
        self::$DIR_INTEGRATION          = self::$DIR_APP . '/Integration';
        self::$DIR_INTEGRATION_SQLMAP   = self::$DIR_INTEGRATION . '/Sqlmap';

        // initialized
        self::$IS_INITIALIZED_DIRECTORY = true;
    }



    /**
     * configure initialize
     *
     * @param string $path_configure
     * @return void
     * @throws ConfigureException
     */
    public static function configure(string $path_configure): void
    {
        $configures = include($path_configure);

        // ルーティング処理初期化
        Router::sharedInstance()->loadConfigures($configures);

        // 認証処理初期化
        Authentication::sharedInstance()->loadConfigures($configures);

        // メッセージ処理初期化
        Message::sharedInstance()->loadConfigures($configures);

        // ロガー処理
        Logger::sharedInstance()->loadConfigures($configures);

        // アプリケーション
        Application::sharedInstance()->loadConfigures($configures);

        // パス
        Paths::sharedInstance()->loadConfigures($configures);
    }
}
