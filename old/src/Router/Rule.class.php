<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Router;

use Citrus\Configure\Configurable;
use Citrus\Variable\Singleton;
use Citrus\Variable\Structs;

/**
 * ルーティングルール
 */
class Rule extends Configurable
{
    use Singleton;
    use Structs;

    /** @var string */
    public $default;

    /** @var string */
    public $login;

    /** @var string */
    public $error404;

    /** @var string */
    public $error503;



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
     * {@inheritDoc}
     */
    protected function configureKey(): string
    {
        return 'rule';
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
        return [
            'default',
            'login',
            'error404',
            'error503',
        ];
    }
}
