<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus;

use Citrus\Variable\Structs;

/**
 * コマンド処理
 */
class Command
{
    use Structs;

    /** @var string script code */
    public $script = '';

    /** @var array configure */
    public $configures = [];

    /** @var array command options */
    protected $options = [];

    /** @var array command parameters */
    protected $parameters = [];



    /**
     * コマンドライン引数のパース処理
     *
     * @return void
     */
    public function options(): void
    {
        // コマンドライン引数がなければ処理スキップ
        if (0 === count($this->options))
        {
            return;
        }

        $this->parameters = getopt('', $this->options);
    }



    /**
     * コマンド実行処理
     *
     * @return void
     */
    public function execute(): void
    {
    }



    /**
     * コマンド実行前処理
     *
     * @return void
     */
    public function before(): void
    {
    }



    /**
     * コマンド実行後処理
     *
     * @return void
     */
    public function after(): void
    {
    }



    /**
     * コマンドラインオプションパラメータ取得
     *
     * @param string      $key     パラメータキー
     * @param string|null $default デフォルト値
     * @return string|null パラメータ値
     */
    public function parameter(string $key, ?string $default = null): ?string
    {
        return ($this->parameters[$key] ?? $default);
    }



    /**
     * コマンドランナー
     *
     * @param array $configures 設定情報
     * @return void
     */
    public static function runner(array $configures): void
    {
        $command = new static();
        $command->configures = $configures;
        $command->options();
        $command->before();
        $command->execute();
        $command->after();
    }
}
