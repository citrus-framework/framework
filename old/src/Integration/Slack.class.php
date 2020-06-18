<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Integration;

use Citrus\Collection;
use Citrus\Configure\Configurable;
use Citrus\Http;
use Citrus\Integration\Slack\Attachments;
use Citrus\Variable\Singleton;

/**
 * 外部統合Slack処理
 */
class Slack extends Configurable
{
    use Singleton;

    /** @var array webhook_urls */
    private $webhook_urls = [];



    /**
     * {@inheritDoc}
     */
    public function loadConfigures(array $configures = []): Configurable
    {
        // 設定配列の読み込み
        parent::loadConfigures($configures);

        $this->webhook_urls = Collection::stream($this->configures)->keyMap(function ($vl) {
            return $vl['webhook_url'];
        })->toList();

        return $this;
    }



    /**
     * slackに投稿
     *
     * @param string      $key  slack設定のキー
     * @param Attachments $item
     * @return void
     */
    public function send(string $key, Attachments $item): void
    {
        $slack_data = [
            'attachments' => [$item->properties()],
        ];

        Http::post($this->webhook_urls[$key], json_encode($slack_data));
    }



    /**
     * WEBHOOK URLの取得
     *
     * @param string $key slack設定のキー
     * @return string
     */
    public function webhookURL(string $key): string
    {
        return ($this->webhook_urls[$key] ?? '');
    }



    /**
     * slack attachments fields 用の配列に変換
     *
     * @param string $title
     * @param string $value
     * @param bool $short
     * @return array
     */
    public static function toFields(string $title, string $value, bool $short = true)
    {
        return [
            'title' => $title,
            'value' => $value,
            'short' => $short,
        ];
    }



    /**
     * {@inheritDoc}
     */
    protected function configureKey(): string
    {
        return 'slack';
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
}
