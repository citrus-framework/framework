<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Configure;

use Citrus\Variable\Singleton;
use Citrus\Variable\Structs;

/**
 * アプリケーション定義
 */
class Application extends Configurable
{
    use Singleton;
    use Structs;

    /** @var string */
    public $id;

    /** @var string */
    public $name;

    /** @var string */
    public $path;

    /** @var string */
    public $copyright;

    /** @var string */
    public $domain;



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
        return 'application';
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
            'id',
            'path',
        ];
    }
}
