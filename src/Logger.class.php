<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus;

use Citrus\Configure\Configurable;
use Citrus\Logger\CloudWatchLogs;
use Citrus\Logger\File;
use Citrus\Logger\Level;
use Citrus\Logger\LogType;
use Citrus\Logger\Syslog;
use Citrus\Variable\Singleton;

/**
 * ログ処理
 */
class Logger extends Configurable
{
    use Singleton;

    /** @var LogType ログタイプ別のインスタンス */
    public $logType;



    /**
     * {@inheritDoc}
     * @throws \Exception
     */
    public function loadConfigures(array $configures = []): Configurable
    {
        // 設定配列の読み込み
        parent::loadConfigures($configures);

        // タイプによって生成インスタンスを分ける
        switch ($this->configures['type'])
        {
            // file
            case LogType::FILE:
                $this->logType = new File($this->configures);
                break;
            // syslog
            case LogType::SYSLOG:
                $this->logType = new Syslog($this->configures);
                break;
            // AWS CloudWatch
            case LogType::CLOUDWATCHLOGS:
                $this->logType = new CloudWatchLogs($this->configures);
                break;
            default:
                throw new \Exception('Loggerの生成に失敗しました');
        }

        return $this;
    }



    /**
     * trace log file
     *
     * @param mixed $value
     */
    public static function trace($value)
    {
        self::sharedInstance()->output(Level::TRACE, $value, func_get_args());
    }



    /**
     * debug log file
     *
     * @param mixed $value
     */
    public static function debug($value)
    {
        self::sharedInstance()->output(Level::DEBUG, $value, func_get_args());
    }



    /**
     * info log file
     *
     * @param mixed $value
     */
    public static function info($value)
    {
        self::sharedInstance()->output(Level::INFO, $value, func_get_args());
    }



    /**
     * warn log file
     *
     * @param mixed $value
     */
    public static function warn($value)
    {
        self::sharedInstance()->output(Level::WARNING, $value, func_get_args());
    }



    /**
     * error log file
     *
     * @param mixed $value
     */
    public static function error($value)
    {
        self::sharedInstance()->output(Level::ERROR, $value, func_get_args());
    }



    /**
     * fatal log file
     *
     * @param mixed $value
     */
    public static function fatal($value)
    {
        self::sharedInstance()->output(Level::FATAL, $value, func_get_args());
    }



    /**
     * output log file
     *
     * @param string $level  ログレベル
     * @param mixed  $value  ログの内容
     * @param array  $params パラメータ
     * @return void
     */
    public function output(string $level, $value, array $params): void
    {
        // ログレベルによる出力許容チェック
        if (false === self::isOutputableLevel($level))
        {
            return;
        }

        // params
        array_shift($params);

        // display
        if (true === $this->configures['display'])
        {
            $display_value = $value;
            if (true === is_string($value))
            {
                $display_value = vsprintf($value, $params);

            }
            var_dump([
                Citrus::$TIMESTAMP_FORMAT,
                $display_value,
            ]);

        }
        $this->logType->output($level, $value, $params);
    }



    /**
     * コンフィグ設定で指定されたログを出力するレベルか判定する
     *
     * @param string $level ログレベル
     * @return bool
     */
    public function isOutputableLevel(string $level): bool
    {
        // 出力設定のログレベル
        $configure_level_index = array_search($this->configures['level'], Level::$LEVELS, true);
        // 出力しようとしているログレベル
        $target_level_index = array_search($level, Level::$LEVELS, true);
        // 出力設定のログレベル <= 出力しようとしているログレベル
        return ($configure_level_index <= $target_level_index);
    }



    /**
     * {@inheritDoc}
     */
    protected function configureKey(): string
    {
        return 'logger';
    }



    /**
     * {@inheritDoc}
     */
    protected function configureDefaults(): array
    {
        // ロガータイプ
        $type = ($this->configures['type'] ?? null);

        // 共通
        $defaults = [
            'level' => Level::INFO,
            'display' => false,
        ];

        // ファイルの場合
        if (LogType::FILE === $type)
        {
            $defaults += [
                'owner' => posix_getpwuid(posix_geteuid())['name'],
                'group' => posix_getgrgid(posix_getegid())['name'],
            ];
        }

        return $defaults;
    }



    /**
     * {@inheritDoc}
     */
    protected function configureRequires(): array
    {
        // ロガータイプ
        $type = ($this->configures['type'] ?? null);

        // 共通
        $requires = [
            'type',
        ];

        // ファイルの場合
        if (LogType::FILE === $type)
        {
            $requires += [
                'directory',
                'filename',
                'owner',
                'group',
            ];
        }

        return $requires;
    }
}
