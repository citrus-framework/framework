<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Logger;

use Citrus\Variable\Structs;

/**
 * Syslog出力ロガー
 */
class Syslog implements LogType
{
    use Structs;

    /** @var string */
    public $directory;

    /** @var string */
    public $filename;



    /**
     * constructor
     *
     * @param array $configure
     */
    public function __construct(array $configure = [])
    {
        $this->bind($configure);
    }



    /**
     * output log syslog
     *
     * @param string $level  ログレベル
     * @param mixed  $value  ログ内容
     * @param array  $params パラメーター
     */
    public function output(string $level, $value, array $params = [])
    {
        $vl_dump = '';
        if (is_string($value))
        {
            $vl_dump = vsprintf($value, $params) . PHP_EOL;
        }
        else
        {
            ob_start();
            var_dump($value);
            $vl_dump = ob_get_contents();
            ob_end_clean();
        }

        $dat = date('[Y-m-d H:i:s] ', $_SERVER['REQUEST_TIME']).htmlspecialchars_decode(strip_tags($vl_dump));

        syslog(LOG_INFO, $dat);
    }
}
