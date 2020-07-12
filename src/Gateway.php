<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus;

use Citrus\Configure\Application;
use Citrus\Controller\WebController;
use Citrus\Http\Header;

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
     * @param string|null $type リクエストタイプ
     */
    public static function main(string $type = null): void
    {
        // security null byte replace
        $search = "\0";
        $replace = '';
        foreach ($_GET as &$one)     { $one = str_replace($search, $replace, $one); }
        foreach ($_POST as &$one)    { $one = str_replace($search, $replace, $one); }
        foreach ($_REQUEST as &$one) { $one = str_replace($search, $replace, $one); }

        // セッション処理開始
        switch ($type)
        {
            case self::TYPE_CONTROLLER:
                Session::factory(true);
                self::controller();
                break;
            case self::TYPE_COMMAND:
                Session::part();
                self::command();
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
            $router = Router::sharedInstance()->factory();
            // コントローラー名前空間
            $controller_namespace = '\\' . ucfirst(Application::sharedInstance()->id);
            // クラスパス
            $class_path = $controller_namespace . '\\Controller' . $router->toClassPath('Controller');

            /** @var WebController $controller */
            $controller = new $class_path();
            $controller->run();

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
     */
    protected static function command(): void
    {

//        $command = new static();
//        $command->configures = $configures;
//        $command->options();
//        $command->before();
//        $command->execute();
//        $command->after();


//        try
//        {
//            $command = Command::callCommand();
//            $command->before();
//            $command->execute();
//            $command->after();
//        }
//        catch (SqlmapException $e)
//        {
//            Logger::debug($e);
//        }
//        catch (AutoloaderException $e)
//        {
//            Logger::debug($e);
//        }
    }



//    /**
//     * ドキュメントのパスを取得する
//     *
//     * @param string $gateway_type ゲートウェイタイプ
//     * @return string
//     */
//    protected static function documentPath(string $gateway_type): string
//    {
//        // コントローラー
//        if (self::TYPE_CONTROLLER === $gateway_type)
//        {
//            // ルートアイテム
//            $router_item = Router::sharedInstance()->factory();
//
//            // プロトコル
//            $protocol = $router_item->protocol;
//            $protocol_code = ucfirst(strtolower($protocol->))
//
//            $document = $router_item->document;
//            $action = $router_item->action;
////            $device_code = $router_item->get('device');
////            $document_code = $router_item->get('document');
//
//
//
//
//            // ドキュメントコード
//            $ucfirst_document_codes = [];
//            foreach (explode('-', $document_code) as $one)
//            {
//                $ucfirst_code = ucfirst($one);
//                $ucfirst_document_codes[] = $ucfirst_code;
//            }
//
//            // 頭文字だけ大文字で後は小文字のterm
//            $ucfirst_device_code = ucfirst(strtolower($device_code));
//
//            // 頭文字だけ大文字で後は小文字のAPPLICATION_CD
//            $ucfirst_application_id = ucfirst(Application::sharedInstance()->id);
//
//            // 末尾を取り除く
//            $ucfirst_document_code = array_pop($ucfirst_document_codes);
//            $controller_namespace = '\\' . $ucfirst_application_id . '\\Controller\\' . $ucfirst_device_code;
//            foreach ($ucfirst_document_codes as $one)
//            {
//                $controller_namespace .= ('\\' . $one);
//            }
//            $controller_class_name = $ucfirst_document_code . 'Controller';
//
//            // I have control
//            $controller_namespace_class_name = $controller_namespace . '\\' . $controller_class_name;
//            /** @var Page $controller */
//            $controller = new $controller_namespace_class_name();
//            $controller->run();
//
//
//
//            $request = new Request();
//            $request->requestPath();
//        }
//    }
}
