<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus;

use Citrus\Configure\Application;
use Citrus\Controller\ApiController;
use Citrus\Http\Header;
use Citrus\Http\Server\Request;

/**
 * ゲートウェイ処理
 */
class Gateway
{
    /** @var string controller */
    public const TYPE_CONTROLLER = 'controller';

    /** @var string command */
    public const TYPE_COMMAND = 'command';


    /**
     * gateway main logic
     *
     * @param string|null $type       リクエストタイプ
     * @param array       $configures 設定配列
     */
    public static function main(string|null $type = null, array $configures = []): void
    {
        // security null byte replace
        $search = "\0";
        $replace = '';
        foreach ($_GET as &$one)
        {
            $one = str_replace($search, $replace, $one);
        }
        foreach ($_POST as &$one)
        {
            $one = str_replace($search, $replace, $one);
        }
        foreach ($_REQUEST as &$one)
        {
            $one = str_replace($search, $replace, $one);
        }

        // セッション処理開始
        switch ($type)
        {
            case self::TYPE_CONTROLLER:
                Session::factory(true);
                self::controller();
                break;
            case self::TYPE_COMMAND:
                Session::part();
                self::command($configures);
                break;
            default:
        }
    }

    /**
     * controller main logic
     */
    protected static function controller(): void
    {
        try
        {
            // ルーター
            $router = Router::sharedInstance()->factory(Request::generate());
            // コントローラー名前空間
            $controller_namespace = '\\' . ucfirst(Application::sharedInstance()->id);
            // クラスパス
            $class_path = $controller_namespace . '\\Controller' . $router->toClassPath('Controller');

            /** @var ApiController $controller */
            $controller = new $class_path();
            $controller->run($router);

            // save controller
            Session::commit();
        }
        catch (\Exception $e)
        {
            // 404でリダイレクトの様に振る舞う
            Header::status404();
//            Session::$router = (new Item(Router::sharedInstance()->protocol))->parse(
//                Rule::sharedInstance()->error404
//            );
            self::controller();
        }
    }

    /**
     * cli command main logic
     *
     * @param array $configures 設定配列
     */
    protected static function command(array $configures): void
    {
        // コマンドから指定したクラス
        $options = getopt('', ['command:']);
        $command_class = $options['command'];
        // コントローラー名前空間
        $controller_namespace = '\\' . ucfirst(Application::sharedInstance()->id);
        /** @var Console $class_path クラスパス */
        $class_path = $controller_namespace . '\\Command\\' . $command_class . 'Command';

        // コマンド実行
        $class_path::runner($configures);
    }
}
