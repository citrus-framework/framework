<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus;

use Citrus\Configure\Application;
use Citrus\Controller\Page;
use Citrus\Http\Header;
use Citrus\Router\Item;
use Citrus\Router\Rule;

/**
 * ゲートウェイ処理
 */
class Gateway
{
    /** @var string controller */
    const TYPE_CONTROLLER = 'controller';

    /** @var string command */
    const TYPE_COMMAND = 'command';




    /**
     * gateway main logic
     *
     * @param string|null $type リクエストタイプ
     */
    public static function main(string $type = null)
    {
        // security nullbyte replace
        $search = "\0";
        $replace = '';
        foreach ($_GET as &$one)     { $one = str_replace($search, $replace, $one); }
        foreach ($_POST as &$one)    { $one = str_replace($search, $replace, $one); }
        foreach ($_REQUEST as &$one) { $one = str_replace($search, $replace, $one); }

        // logic selecter
        if (self::TYPE_CONTROLLER === $type)
        {
            // セッション処理開始
            Session::factory(true);
            self::controller();
        }
        else if (self::TYPE_COMMAND === $type)
        {
            // セッション処理開始
            Session::part();
            self::command();
        }
    }



    /**
     * controller main logic
     */
    protected static function controller()
    {
        try
        {
            // ルートパース
            $device_code = Session::$router->get('device');
            $document_code = Session::$router->get('document');

            // ドキュメントコード
            $ucfirst_document_codes = [];
            foreach (explode('-', $document_code) as $one)
            {
                $ucfirst_code = ucfirst($one);
                $ucfirst_document_codes[] = $ucfirst_code;
            }

            // 頭文字だけ大文字で後は小文字のterm
            $ucfirst_device_code = ucfirst(strtolower($device_code));

            // 頭文字だけ大文字で後は小文字のAPPLICATION_CD
            $ucfirst_application_id = ucfirst(Application::sharedInstance()->id);

            // 末尾を取り除く
            $ucfirst_document_code = array_pop($ucfirst_document_codes);
            $controller_namespace = '\\' . $ucfirst_application_id . '\\Controller\\' . $ucfirst_device_code;
            foreach ($ucfirst_document_codes as $one)
            {
                $controller_namespace .= ('\\' . $one);
            }
            $controller_class_name = $ucfirst_document_code . 'Controller';

            // I have control
            $controller_namespace_class_name = $controller_namespace . '\\' . $controller_class_name;
            /** @var Page $controller */
            $controller = new $controller_namespace_class_name();
            $controller->run();

            // save controller
            Session::commit();
        }
        catch (\Exception $e)
        {
            // 404でリダイレクトの様に振る舞う
            Header::status404();
            Session::$router = (new Item(Router::sharedInstance()->device))->parse(
                Rule::sharedInstance()->error404
            );
            self::controller();
        }
    }



    /**
     * cli command main logic
     */
    protected static function command()
    {
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
}
