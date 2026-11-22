<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus;

use Citrus\Configure\Configurable;
use Citrus\Http\Server\Request;
use Citrus\Router\Protocol;
use Citrus\Variable\Binders;
use Citrus\Variable\Singleton;

/**
 * ドキュメントのルーティング処理
 */
class Router extends Configurable
{
    use Singleton;
    use Binders;

    /** @var Protocol */
    public Protocol $protocol;

    /** @var string[] */
    public array $documents = [];

    /** @var string */
    public string $action;

    /** @var array */
    public array $parameters;

    /** @var Protocol[] プロトコル一覧 */
    public static array $PROTOCOLS = [
        Protocol::API,
        Protocol::PC,
        Protocol::SP,
    ];

    /**
     * factory
     * @param Request|null $request
     * @return $this
     */
    public function factory(Request|null $request = null): self
    {
        // URLがない場合はconfigureのdefaultを取得
        $request_url = ($request?->requestPath()
            ?? parse_url($_SERVER['REQUEST_URI'])['path']
            ?? $this->configures['default_url']);

        // URLをパース
        $this->parse($request_url);

        // パラメータ
        $this->parameters = $request->gets();

        return $this;
    }

    /**
     * url parse
     * @param string|null $url
     * @return $this
     */
    public function parse(string|null $url = null): self
    {
        // 分割
        $parts = explode('/', $url);
        // /で始まっている場合、
        // /で終わっている場合を考慮
        // 空の要素を排除
        $parts = Collection::fromArray($parts)->filter(static fn ($vl) => ($vl !== ''))->toValues();

        // 要素の最初がプロトコルリストにある場合はそれを選択
        // 要素が無いときは current が false を返すので、文字列にすると空文字になる
        $protocol = Protocol::tryFrom(strtolower((string)current($parts)));
        if (in_array($protocol, self::$PROTOCOLS, true) === true)
        {
            // リストにある場合は最初の要素を削除する
            $this->protocol = $protocol;
            array_shift($parts);
        }
        // プロトコルリストにない場合はユーザーエージェント判定する
        else
        {
            $this->protocol = (Useragent::isMobile() === true ? Protocol::SP : Protocol::PC);
        }

        // ルーティング要素が１つしか無い場合はデフォルトでindexをつける
        if (count($parts) === 1)
        {
            $parts[] = 'index';
        }

        // 最終要素がactionになる
        $this->action = array_pop($parts);
        // それ以外の残った要素がdocumentになる
        $this->documents = $parts;

        return $this;
    }

    /**
     * リクエストからクラスパスを生成する
     * @param string|null $suffix クラス名接尾辞
     * @return string
     */
    public function toClassPath(string|null $suffix = ''): string
    {
        // パーツをスタックしていく
        $parts = self::toUcFirsts(array_merge([$this->protocol->value], $this->documents));

        // 文字列化して返却 \Hoge\fuga の様な文字列
        return '\\' . implode('\\', $parts) . $suffix;
    }

    /**
     * リクエストからファイルパスを生成する
     * @return string[]
     */
    public function toUcFirstPaths(): array
    {
        // パーツをスタックしていく
        return self::toUcFirsts(array_merge([$this->protocol->value], $this->documents, [$this->action]));
    }

    /**
     * {@inheritDoc}
     */
    protected function configureDefaults(): array
    {
        return [
            'default_url' => 'home/index',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function configureRequires(): array
    {
        return [
            'default_url',
        ];
    }

    /**
     * 各要素を、先頭だけ大文字にする
     * @param string[] $parts 要素
     * @return string[]
     */
    private static function toUcFirsts(array $parts): array
    {
        return array_map(static fn (string $vl) => ucfirst(strtolower($vl)), $parts);
    }
}
