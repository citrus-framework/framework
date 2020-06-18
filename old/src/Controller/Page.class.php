<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Controller;

use Citrus\CitrusException;
use Citrus\Configure;
use Citrus\Configure\Application;
use Citrus\Configure\ConfigureException;
use Citrus\Configure\Paths;
use Citrus\Document\Pagecode;
use Citrus\Formmap;
use Citrus\Http\Header;
use Citrus\Library\Smarty3;
use Citrus\Message;
use Citrus\Router\Item;
use Citrus\Router\Rule;
use Citrus\Session;
use Citrus\Variable\Structs;
use Exception;
use Smarty_Internal_Template;

/**
 * ページ処理
 */
class Page
{
    use Structs;

    /** @var Pagecode */
    protected $pagecode;

    /** @var Smarty3 */
    protected $smarty = null;

    /** @var Formmap */
    protected $formmap = null;



    /**
     * controller run
     *
     * @throws CitrusException
     */
    public function run()
    {
        try
        {
            // ルーター
            $router = clone Session::$router;
            // 実行アクション
            $actionName = $router->action;
            if (false === method_exists($this, $actionName))
            {
                $actionName = 'none';
                $router->action = $actionName;
                if (false === method_exists($this, $actionName))
                {
                    $router404 = $router->parse(Rule::sharedInstance()->error404);
                    $actionName = $router404->action;
                    $router->document = $router404->document;
                    $router->action = $actionName;
                }
            }

            // 初期化実行
            $templateRouterInitialize = $this->initialize();
            $router = $templateRouterInitialize ?: $router;

            // アクション実行
            $templateRouterAction = $this->$actionName();
            $router = $templateRouterAction ?: $router;

            // 後片付け
            $templateRouterReleace = $this->release();
            $router = $templateRouterReleace ?: $router;

            // form値のbind
            $this->callFormmap()->bind();
            // テンプレート当て込み
            $message = Message::sharedInstance();
            $this->callSmarty()->assign('router', Session::$router);
            $this->callSmarty()->assign('pagecode', $this->callPagecode());
            $this->callSmarty()->assign('formmap', $this->callFormmap());
            $this->callSmarty()->assign('errors', $message->popErrors());
            $this->callSmarty()->assign('messages', $message->popMessages());
            $this->callSmarty()->assign('successes', $message->popSuccesses());

            // セッションのコミット
            Session::commit();

            // リソース読み込み
            $this->loadResource($router);

            // テンプレート読み込み
            $this->loadTemplate($router);
        }
        catch (CitrusException $e)
        {
            Header::status404();
            throw $e;
        }
        catch (Exception $e)
        {
            Header::status404();
            throw CitrusException::convert($e);
        }
    }



    /**
     * テンプレート読み込んで表示
     *
     * @param Smarty_Internal_Template|Smarty3|null $template
     * @param Item|null                             $router_item
     * @throws CitrusException|\SmartyException
     */
    public static function displayTemplate($template, Item $router_item = null)
    {
        $router_item = $router_item ?: Session::$router;

        $templateDocumentArray = explode('_', str_replace('-', '_', $router_item->document));
        $templateArray = [$router_item->device];
        foreach ($templateDocumentArray as $templateDocument)
        {
            $templateArray[] = $templateDocument;
        }
        $templateArray[] = $router_item->action;

        foreach ($templateArray as $ky => $vl)
        {
            $templateArray[$ky] = ucfirst($vl);
        }

        // テンプレート読み込み
        $template_path = Paths::sharedInstance()->callTemplate('/Page') . '/' . implode('/', $templateArray).'.tpl';
        if (false === file_exists($template_path))
        {
            throw new CitrusException(sprintf('[%s]のテンプレートが存在しません。', $template_path));
        }
        $template->display($template_path);
    }



    /**
     * initialize method
     *
     * @return Item|null
     */
    protected function initialize()
    {
        return null;
    }



    /**
     * release method
     *
     * @return Item|null
     */
    protected function release()
    {
        return null;
    }


    /**
     * 404 method
     *
     * @return Item|null
     */
    protected function error404()
    {
        return null;
    }



    /**
     * Pagecodeを生成して取得
     *
     * @return Pagecode
     */
    protected function callPagecode(): Pagecode
    {
        if (true === is_null($this->pagecode))
        {
            $app = Application::sharedInstance();
            $pagecode = new Pagecode();
            $pagecode->site_id = $app->id;
            $pagecode->site_title = $app->name;
            $pagecode->copyright = $app->copyright;

            $this->pagecode = $pagecode;
        }
        return $this->pagecode;
    }



    /**
     * call formmap element
     *
     * @return Formmap
     * @throws ConfigureException
     */
    protected function callFormmap(): Formmap
    {
        if (true === is_null($this->formmap))
        {
            $this->formmap = Formmap::sharedInstance()
                ->loadConfigures(Configure::$CONFIGURES);
        }
        return $this->formmap;
    }



    /**
     * call smarty element
     *
     * @return Smarty3
     */
    protected function callSmarty(): Smarty3
    {
        if (is_null($this->smarty) === true)
        {
            $this->smarty = new Smarty3();
        }
        return $this->smarty;
    }



    /**
     * リソース読み込み
     *
     * @param Item|null $router_item
     */
    private function loadResource(Item $router_item = null)
    {
        $router_item = $router_item ?: Session::$router;

        // リソース配列用パス
        $resourceDocumentList = explode('_', str_replace('-', '_', $router_item->document));
        $resourceList = [$router_item->device];
        foreach ($resourceDocumentList as $ky => $vl)
        {
            $resourceList[] = $vl;
        }
        $resourceList[] = $router_item->action;
        foreach ($resourceList as $ky => $vl)
        {
            $resourceList[$ky] = ucfirst(strtolower($vl));
        }

        // stylesheet, javascript
        $resourceAppendedList = [];
        foreach ($resourceList as $ky => $vl)
        {
            $resourceAppendedList[] = $vl;
            $path = '/' . implode('/', $resourceAppendedList);
            $this->callPagecode()->addStylesheet($path . '.css');
            $this->callPagecode()->addJavascript($path . '.js');
        }

        // プラグイン
        $this->callSmarty()->addPluginsDir([Paths::sharedInstance()->callTemplate('/Plug')]);
    }



    /**
     * テンプレート読み込み
     *
     * @param Item|null $router_item
     * @throws CitrusException|\SmartyException
     */
    private function loadTemplate(Item $router_item = null)
    {
        $router_item = $router_item ?: Session::$router;

        self::displayTemplate($this->callSmarty(), $router_item);
    }
}
