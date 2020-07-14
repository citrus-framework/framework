<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Document;

use Citrus\Collection;
use Citrus\Configure\Application;
use Citrus\Configure\Paths;
use Citrus\Logger;
use Citrus\Variable\Dates;
use Citrus\Variable\VariableException;

/**
 * ページコード処理
 */
class Pagecode
{
    /** @var string html title */
    public $page_title = '';

    /** @var string html sub title */
    public $page_subtitle = '';

    /** @var string site title */
    public $site_title = '';

    /** @var string internal site id */
    public $site_id = '';

    /** @var string[] meta keywords */
    public $meta_keywords = [];

    /** @var string[] meta descriptions */
    public $meta_descriptions = [];

    /** @var string[] パンくずリストの配列 */
    public $breadcrumbs = [];

    /** @var string[] Javascript配列 */
    public $javascripts = [];

    /** @var string[] Stylesheet配列 */
    public $stylesheets = [];

    /** @var string stylesheet plaintext */
    public $stylesheet_plaintext = '';

    /** @var string copyright */
    public $copyright;

    /** @var string template id */
    public $template_id;

    /** @var string stylesheet, javascript suffix */
    public $suffix = '';



    /**
     * constructor.
     */
    public function __construct()
    {
        try
        {
            $this->suffix = Dates::now()->format('U');
        }
        catch (VariableException $e)
        {
            Logger::error($e);
        }
    }



    /**
     * Javascriptの追加
     *
     * @param string|string[] $javascript
     * @return $this
     */
    public function addJavascript($javascript): self
    {
        // 配列の場合は再起する
        if (true === is_array($javascript))
        {
            foreach ($javascript as $path)
            {
                $this->addJavascript($path);
            }
            return $this;
        }

        // アプリケーションパス
        $app_path = Application::sharedInstance()->path;
        // パス定義
        $paths = Paths::sharedInstance();

        // パスが絶対パスの場合
        if (true === file_exists($javascript))
        {
            // 絶対パスを削除
            $path = str_replace($app_path, '', $javascript);
            $this->javascripts[] = $path;
            return $this;
        }

        // パスがベースディレクトリ以下指定の場合
        $path = ($app_path . $javascript);
        if (true === file_exists($path))
        {
            // 絶対パスを削除
            $path = str_replace($app_path, '', $path);
            $this->javascripts[] = $path;
            return $this;
        }

        // パスがライブラリの可能性
        $path = $paths->callJavascriptLibrary($javascript);
        if (true === file_exists($path))
        {
            $this->addJavascript($path);
            return $this;
        }

        // パスが独自追加の場合
        $path = $paths->callJavascript($javascript);
        if (true === file_exists($path))
        {
            $this->addJavascript($path);
            return $this;
        }

        // ページ用リソースの場合
        $path = $paths->callJavascript('/Page' . $javascript);
        if (true === file_exists($path))
        {
            $this->addJavascript($path);
            return $this;
        }

        // 外部リソースの場合
        $path = $javascript;
        if (0 === strpos($javascript, 'http'))
        {
            $this->javascripts[] = $path;
            return $this;
        }

        return $this;
    }



    /**
     * Stylesheetの追加
     *
     * @param string|string[] $stylesheet
     * @return $this
     */
    public function addStylesheet($stylesheet): self
    {
        // 配列の場合は再起する
        if (true === is_array($stylesheet))
        {
            foreach ($stylesheet as $path)
            {
                $this->addStylesheet($path);
            }
            return $this;
        }

        // アプリケーションパス
        $app_path = Application::sharedInstance()->path;
        // パス定義
        $paths = Paths::sharedInstance();

        // パスが絶対パスの場合
        if (true === file_exists($stylesheet))
        {
            // 絶対パスを削除
            $path = str_replace($app_path, '', $stylesheet);
            $this->stylesheets[] = $path;
            return $this;
        }

        // パスがベースディレクトリ以下指定の場合
        $path = ($app_path . $stylesheet);
        if (true === file_exists($path))
        {
            // 絶対パスを削除
            $path = str_replace($app_path, '', $path);
            $this->stylesheets[] = $path;
            return $this;
        }

        // パスがライブラリの可能性
        $path = $paths->callStylesheetLibrary($stylesheet);
        if (true === file_exists($path))
        {
            $this->addStylesheet($path);
            return $this;
        }

        // パスが独自追加の場合
        $path = $paths->callStylesheet($stylesheet);
        if (true === file_exists($path))
        {
            $this->addStylesheet($path);
            return $this;
        }

        // ページ用リソースの場合
        $path = $paths->callStylesheet('/Page' . $stylesheet);
        if (true === file_exists($path))
        {
            $this->addStylesheet($path);
            return $this;
        }

        // 外部リソースの場合
        $path = $stylesheet;
        if (0 === strpos($stylesheet, 'http'))
        {
            $this->stylesheets[] = $path;
            return $this;
        }

        return $this;
    }



    /**
     * ファイルのStylesheetをプレーンなStylesheetにして追加
     *
     * @param string $stylesheet
     * @return void
     */
    public function addStylesheetPlaintext($stylesheet): void
    {
        if (true === file_exists($stylesheet))
        {
            $content = str_replace("\r\n", "\n", file_get_contents($stylesheet));
            $this->stylesheet_plaintext .= preg_replace('#/\*/?(\n|[^/]|[^*]/)*\*/#', '', $content);
        }
    }



    /**
     * パンくずリストの追加
     *
     * @param string      $name 名称
     * @param string|null $url  URL
     * @return $this
     */
    public function addBreadcrumbs(string $name, string $url = ''): self
    {
        $this->breadcrumbs[] = [$name => $url];

        return $this;
    }



    /**
     * minimize javascript
     * 同じファイルパスにミニマイズ版があれば置き換える
     *
     * @return $this
     */
    public function replaceMinJavascript(): self
    {
        // アプリケーションパス
        $app_path = Application::sharedInstance()->path;

        // ファイルリストを生成して反映
        $this->javascripts = Collection::stream($this->javascripts)->map(function ($vl) use ($app_path) {
            // ファイル名にmin.jsが含まれる
            if (false !== strpos($vl, '.min.js'))
            {
                return $vl;
            }

            // 含まれない場合は、ファイルを探す
            $min_path = str_replace('.js', '.min.js', $vl);
            if (true === file_exists($app_path . $min_path))
            {
                return $min_path;
            }

            // 見つからなければそのまま
            return $vl;
        })->toList();

        return $this;
    }



    /**
     * minimize stylesheet
     * 同じファイルパスにミニマイズ版があれば置き換える
     *
     * @return $this
     */
    public function replaceMinStylesheet(): self
    {
        // アプリケーションパス
        $app_path = Application::sharedInstance()->path;

        // ファイルリストを生成して反映
        $this->stylesheets = Collection::stream($this->stylesheets)->map(function ($vl) use ($app_path) {
            // ファイル名にmin.cssが含まれる
            if (false !== strpos($vl, '.min.css'))
            {
                return $vl;
            }

            // 含まれない場合は、ファイルを探す
            $min_path = str_replace('.css', '.min.css', $vl);
            if (true === file_exists($app_path . $min_path))
            {
                return $min_path;
            }

            // 見つからなければそのまま
            return $vl;
        })->toList();

        return $this;
    }
}
