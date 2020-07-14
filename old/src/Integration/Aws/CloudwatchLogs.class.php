<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Integration\Aws;

use Aws\CloudWatchLogs\CloudWatchLogsClient;
use Citrus\Configure\Configurable;
use Citrus\Variable\Instance;
use Citrus\Variable\Structs;

/**
 * CloudWatch処理
 */
class CloudwatchLogs extends Configurable
{
    use Structs;
    use Instance;

    /** @var string CloudWatchキー logGroupName */
    const LOG_GROUP_NAME = 'logGroupName';

    /** @var string CloudWatchキー logGroupNamePrefix */
    const LOG_GROUP_NAME_PREFIX = 'logGroupNamePrefix';

    /** @var string CloudWatchキー logStreamName */
    const LOG_STREAM_NAME = 'logStreamName';

    /** @var string CloudWatchキー logStreamNamePrefix */
    const LOG_STREAM_NAME_PREFIX = 'logStreamNamePrefix';

    /** @var array AWS接続用パラメーター */
    protected $cloudwatchlogs = [];

    /** @var CloudWatchLogsClient */
    protected $client;

    /** @var array 存在チェックキャッシュ */
    protected $exist_caches = [];

    /** @var array シーケンストークン配列 */
    protected $sequence_tokens = [];



    /**
     * {@inheritDoc}
     */
    public function loadConfigures(array $configures = []): Configurable
    {
        // 設定配列の読み込み
        parent::loadConfigures($configures);

        // 設定のbind
        $this->bindArray($this->configures);

        // クライアントの生成
        $this->client = new CloudWatchLogsClient($this->cloudwatchlogs);

        return $this;
    }



    /**
     * ログを送信する
     *
     * @param string   $log_group_name  ロググループ
     * @param string   $log_stream_name ログストリーム
     * @param string[] $log_events      ログイベント
     * @param bool     $with_regist     ロググループ、ログストリームも一緒に作成する
     * @return void
     */
    public function flush(string $log_group_name, string $log_stream_name, array $log_events, bool $with_regist = false): void
    {
        // ロググループ、ログストリームも一緒に作成する
        if (true === $with_regist)
        {
            $this->registLogStream($log_group_name, $log_stream_name, true);
        }
        // 作成しない場合は、ログストリームが存在しなければ終わり
        else if (false === $this->existLogStream($log_group_name, $log_stream_name))
        {
            return;
        }

        // ログ送信パラメタ
        $params = [
            self::LOG_GROUP_NAME => $log_group_name,
            self::LOG_STREAM_NAME => $log_stream_name,
            'logEvents' => $log_events,
        ];

        // シーケンストークン
        $sequence_token = $this->callSequenceToken($log_group_name, $log_stream_name);
        if (false === is_null($sequence_token))
        {
            $params['sequenceToken'] = $sequence_token;
        }

        // ログ送信
        $response = $this->client->putLogEvents($params);

        // シーケンストークン更新
        if (false === isset($this->sequence_tokens[$log_group_name]))
        {
            $this->sequence_tokens[$log_group_name] = [];
        }
        $this->sequence_tokens[$log_group_name][$log_stream_name] = $response->get('nextSequenceToken');
    }



    /**
     * {@inheritDoc}
     */
    protected function configureKey(): string
    {
        return 'aws';
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
            'cloudwatchlogs',
        ];
    }



    /**
     * ロググループが存在するか否か
     *
     * @param string $log_group_name ロググループ名
     * @return bool
     */
    private function existLogGroup(string $log_group_name): bool
    {
        // 存在情報キャッシュがあれば利用
        if (true === isset($this->exist_caches[$log_group_name]))
        {
            return true;
        }

        // 存在するロググループの取得
        $log_groups = $this->client
            ->describeLogGroups([self::LOG_GROUP_NAME_PREFIX => $log_group_name])
            ->get('logGroups');

        // 存在するかどうか
        foreach ($log_groups as $log_group)
        {
            if ($log_group_name === $log_group[self::LOG_GROUP_NAME])
            {
                // 存在情報をキャッシュする
                $this->exist_caches[$log_group_name] = [];
                return true;
            }
        }

        return false;
    }



    /**
     * ログストリームが存在するか否か
     *
     * @param string $log_group_name  ロググループ名
     * @param string $log_stream_name ログストリーム名
     * @return bool
     */
    private function existLogStream(string $log_group_name, string $log_stream_name): bool
    {
        // 存在チェック
        $exist_group = $this->existLogGroup($log_group_name);
        if (false === $exist_group)
        {
            return false;
        }

        // 存在情報キャッシュがあれば利用
        if (true === isset($this->exist_caches[$log_group_name][$log_stream_name]))
        {
            return true;
        }

        // 存在するログストリームの取得
        $log_streams = $this->client
            ->describeLogStreams([
                self::LOG_GROUP_NAME => $log_group_name,
                self::LOG_STREAM_NAME_PREFIX => $log_stream_name,
                ])
            ->get('logStreams');

        // 存在するかどうか
        foreach ($log_streams as $log_stream)
        {
            if ($log_stream_name === $log_stream[self::LOG_STREAM_NAME])
            {
                // 存在情報をキャッシュする
                $this->exist_caches[$log_group_name][$log_stream_name] = true;
                return true;
            }
        }

        return false;
    }



    /**
     * ロググループの作成
     *
     * @param string $log_group_name ロググループ名
     * @return void
     */
    private function registLogGroup(string $log_group_name): void
    {
        // すでにあれば作らない
        if (true === $this->existLogGroup($log_group_name))
        {
            return;
        }

        // ロググループの作成
        $this->client->createLogGroup([self::LOG_GROUP_NAME => $log_group_name]);
    }



    /**
     * ログストリームの作成
     *
     * @param string $log_group_name  ロググループ名
     * @param string $log_stream_name ログストリーム名
     * @param bool   $with_group      ログストリームと一緒にロググループも作る
     * @return void
     */
    private function registLogStream(string $log_group_name, string $log_stream_name, bool $with_group = false): void
    {
        // ログストリームと一緒にロググループも作る
        if (true === $with_group)
        {
            $this->registLogGroup($log_group_name);
        }

        // すでにあれば作らない
        if (true === $this->existLogStream($log_group_name, $log_stream_name))
        {
            return;
        }

        // ログストリームの作成
        $this->client->createLogStream([
            self::LOG_GROUP_NAME => $log_group_name,
            self::LOG_STREAM_NAME => $log_stream_name,
            ]);
    }



    /**
     * シーケンストークンの取得
     *
     * @param string $log_group_name  ロググループ名
     * @param string $log_stream_name ログストリーム名
     * @return string|null
     */
    private function callSequenceToken(string $log_group_name, string $log_stream_name): ?string
    {
        // シーケンストークンがあれば返す
        if (true === isset($this->sequence_tokens[$log_group_name])
            && true === isset($this->sequence_tokens[$log_group_name][$log_stream_name]))
        {
            return $this->sequence_tokens[$log_group_name][$log_stream_name];
        }

        // シーケンストークンを取得する
        $log_streams = $this->client
            ->describeLogStreams([
                self::LOG_GROUP_NAME => $log_group_name,
                self::LOG_STREAM_NAME_PREFIX => $log_stream_name,
            ])
            ->get('logStreams');

        // 存在するかどうか
        foreach ($log_streams as $log_stream)
        {
            if ($log_stream_name === $log_stream[self::LOG_STREAM_NAME]
                && true === isset($log_stream['uploadSequenceToken']))
            {
                $sequence_token = $log_stream['uploadSequenceToken'];
                $this->sequence_tokens[$log_group_name][$log_stream_name] = $sequence_token;
                return $sequence_token;
            }
        }

        return null;
    }
}
