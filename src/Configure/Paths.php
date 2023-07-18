<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Configure;

use Citrus\Variable\Binders;
use Citrus\Variable\Singleton;

/**
 * パス定義
 */
class Paths extends Configurable
{
    use Binders;
    use Singleton;

    /** @var string */
    public string $cache = '';

    /** @var string */
    public string $compile = '';

    /** @var string */
    public string $template = '';

    /** @var string */
    public string $javascript = '';

    /** @var string */
    public string $javascript_library = '';

    /** @var string */
    public string $stylesheet = '';

    /** @var string */
    public string $stylesheet_library = '';

    /** @var string */
    public string $smarty_plugin = '';



    /**
     * {@inheritDoc}
     */
    public function loadConfigures(array $configures = []): Configurable
    {
        // 設定配列の読み込み
        parent::loadConfigures($configures);

        // 設定のbind
        $this->bindArray($this->configures);

        return $this;
    }



    /**
     * call cache
     *
     * @param string|null $append_path
     * @return string
     */
    public function callCache(string|null $append_path = null): string
    {
        return $this->replace($this->cache, $append_path);
    }



    /**
     * call compile
     *
     * @param string|null $append_path
     * @return string
     */
    public function callCompile(string|null $append_path = null): string
    {
        return $this->replace($this->compile, $append_path);
    }



    /**
     * call template
     *
     * @param string|null $append_path
     * @return string
     */
    public function callTemplate(string|null $append_path = null): string
    {
        return $this->replace($this->template, $append_path);
    }



    /**
     * call javascript
     *
     * @param string|null $append_path
     * @return string
     */
    public function callJavascript(string|null $append_path = null): string
    {
        return $this->replace($this->javascript, $append_path);
    }



    /**
     * call javascript library
     *
     * @param string|null $append_path
     * @return string
     */
    public function callJavascriptLibrary(string|null $append_path = null): string
    {
        return $this->replace($this->javascript_library, $append_path);
    }



    /**
     * call stylesheet
     *
     * @param string|null $append_path
     * @return string
     */
    public function callStylesheet(string|null $append_path = null): string
    {
        return $this->replace($this->stylesheet, $append_path);
    }



    /**
     * call stylesheet library
     *
     * @param string|null $append_path
     * @return string
     */
    public function callStylesheetLibrary(string|null $append_path = null): string
    {
        return $this->replace($this->stylesheet_library, $append_path);
    }



    /**
     * call smarty plugin
     *
     * @param string|null $append_path
     * @return string
     */
    public function callSmartyPlugin(string|null $append_path = null): string
    {
        return $this->replace($this->smarty_plugin, $append_path);
    }



    /**
     * {@inheritDoc}
     */
    protected function configureKey(): string
    {
        return 'paths';
    }



    /**
     * {@inheritDoc}
     */
    protected function configureDefaults(): array
    {
        return [];
    }



    /**
     * {@inheritDoc}
     */
    protected function configureRequires(): array
    {
        return [];
    }



    /**
     * domain など置換用
     *
     * @param string $search
     * @param string $append_path
     * @return string
     */
    private function replace(string $search, string $append_path = ''): string
    {
        $domain = Application::sharedInstance()->domain;
        return str_replace('{#domain#}', $domain, $search) . $append_path;
    }
}
