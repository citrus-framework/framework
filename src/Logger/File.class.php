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
 * ファイル出力ロガー
 */
class File implements LogType
{
    use Structs;

    /** @var string */
    public $directory;

    /** @var string */
    public $filename;

    /** @var string */
    public $owner = 'wwwrun';

    /** @var string */
    public $group = 'www';



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
     * output log file
     *
     * @param string $level  ログレベル
     * @param mixed  $value  ログ内容
     * @param array  $params パラメーター
     * @return void
     */
    public function output(string $level, $value, array $params = []): void
    {
        $_directory = $this->directory;
        $_filename = $this->filename;
        $logfile = sprintf('%s%s-%s', $_directory, $_filename, date('Ymd', $_SERVER['REQUEST_TIME']));

        $vl_dump = '';
        if (true === is_string($value))
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

        // log file exist
        $file_exist = file_exists($logfile);

        // writing log
        $fp = @fopen($logfile, 'a+');
        if ($fp === false && mkdir(dirname($logfile)) === true)
        {
            $fp = fopen($logfile, 'a+');
        }
        fwrite($fp, $dat);
        fclose($fp);

        // file added permission
        if ($file_exist === false)
        {
            chmod($logfile, 0666);
            chown($logfile, $this->owner);
            chgrp($logfile, $this->group);
        }

        clearstatcache();
    }
}
