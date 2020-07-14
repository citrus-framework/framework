<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Controller;

use Citrus\CitrusException;
use Citrus\Configure\Application;
use Citrus\Configure\Paths;
use Citrus\Document\Pagecode;
use Citrus\Http\Header;
use Citrus\Library\Smarty3;
use Citrus\Router;
use Citrus\Session;
use Exception;
use Smarty_Internal_Template;
use SmartyException;

/**
 * Webページ処理
 */
class WebController extends BaseController
{
    /** @var Pagecode */
    protected $pagecode;

    /** @var Smarty3 */
    protected $smarty;



    /**
     * controller run
     *
     * @param Router|null $router ルーティング
     * @throws CitrusException
     */
    public function run(Router $router = null): void
    {
        try
        {
            // ルーター
            $router = ($router ?? Router::sharedInstance()->factory());
            $this->router = $router;
            // 実行アクション
            $action_name = $this->callActionName($router);

            // 初期化実行
            $router = ($this->initialize() ?: $router);
            // アクション実行
            $router = ($this->$action_name() ?: $router);
            // 後片付け
            $router = ($this->release() ?: $router);

            // form値のbind
            $this->callFormmap()->bind();
            // テンプレート当て込み
            $this->callSmarty()->assign('router', $router);
            $this->callSmarty()->assign('pagecode', $this->callPagecode());
            $this->callSmarty()->assign('formmap', $this->callFormmap());

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
     * @param Router|null                           $router
     * @throws CitrusException|SmartyException
     */
    public static function displayTemplate($template, Router $router = null): void
    {
        $router = ($router ?: Router::sharedInstance()->factory());
        $templates = $router->toUcFirstPaths();

        // テンプレート読み込み
        $template_path = sprintf('%s/%s.tpl',
            Paths::sharedInstance()->callTemplate('/Page'),
            implode('/', $templates)
        );
        CitrusException::exceptionElse(
            file_exists($template_path),
            sprintf('[%s]のテンプレートが存在しません。', $template_path)
        );
        $template->display($template_path);
    }



    /**
     * initialize method
     *
     * @return Router|null
     */
    protected function initialize(): ?Router
    {
        return null;
    }



    /**
     * release method
     *
     * @return Router|null
     */
    protected function release(): ?Router
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
     * call smarty element
     *
     * @return Smarty3
     */
    protected function callSmarty(): Smarty3
    {
        $this->smarty = ($this->smarty ?: new Smarty3());
        return $this->smarty;
    }



    /**
     * テンプレート読み込み
     *
     * @param Router $router
     * @throws CitrusException|SmartyException
     */
    private function loadTemplate(Router $router): void
    {
        self::displayTemplate($this->callSmarty(), $router);
    }



    /**
     * リソース読み込み
     *
     * @param Router $router
     */
    private function loadResource(Router $router): void
    {
        // リソース配列用パス
        $resources = $router->toUcFirstPaths();

        // stylesheet, javascript
        $appends = [];
        foreach ($resources as $ky => $vl)
        {
            $appends[] = $vl;
            $path = '/' . implode('/', $appends);
            $this->callPagecode()->addStylesheet($path . '.css');
            $this->callPagecode()->addJavascript($path . '.js');
        }

        // プラグイン
        $this->callSmarty()->addPluginsDir([Paths::sharedInstance()->callTemplate('/Plug')]);
    }



    /**
     * 実行アクション文字列の取得
     *
     * @param Router $router ルーター
     * @return string 実行アクション文字列
     * @throws CitrusException
     */
    private function callActionName(Router $router): string
    {
        $action_name = $router->action;
        if (false === method_exists($this, $action_name))
        {
            $action_name = 'none';
            $router->action = $action_name;
            CitrusException::exceptionElse(
                method_exists($this, $action_name),
                'コントローラーアクションが見つかりません'
            );
        }
        return $action_name;
    }
}
