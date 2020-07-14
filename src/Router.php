<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus;

use Citrus\Configure\Configurable;
use Citrus\Variable\Binders;
use Citrus\Variable\Singleton;

/**
 * ドキュメントのルーティング処理
 */
class Router extends Configurable
{
    use Singleton;
    use Binders;

    /** @var string */
    public const PROTOCOL_API = 'api';

    /** @var string */
    public const PROTOCOL_WEB = 'web';

    /** @var string */
    public const PROTOCOL_PC = 'pc';

    /** @var string */
    public const PROTOCOL_SP = 'sp';

    /** @var string */
    public $protocol;

    /** @var string[] */
    public $documents = [];

    /** @var string */
    public $action;

    /** @var array */
    public $parameters;

    /** @var string[] プロトコル一覧 */
    public static $PROTOCOLS = [
        self::PROTOCOL_API,
        self::PROTOCOL_PC,
        self::PROTOCOL_SP,
    ];



    /**
     * factory
     *
     * @param array|null $request
     * @return $this
     */
    public function factory(array $request = null): self
    {
        // URLがない場合はconfigureのdefaultを取得
        $request['url'] = ($request['url'] ?? $this->configures['default_url']);

        // URLをパース
        $this->parse($request['url']);

        // パラメータ
        $this->parameters = Collection::stream($request)->filter(function ($vl, $ky) {
            return ('url' !== $ky);
        })->toList();

        return $this;
    }



    /**
     * url parse
     *
     * @param string|null $url
     * @return $this
     */
    public function parse(string $url = null): self
    {
        // 分割
        $parts = explode('/', $url);
        // /で始まっている場合、
        // /で終わっている場合を考慮
        $parts = Collection::stream($parts)->filter(function ($vl) {
            // 空の要素を排除
            return ('' !== $vl);
        })->toValues();

        // 要素の最初がプロトコルリストにある場合はそれを選択
        $protocol = strtolower($parts[0] ?? '');
        if (true === in_array($protocol, self::$PROTOCOLS, true))
        {
            // リストにある場合は最初の要素を削除する
            $this->protocol = $protocol;
            array_shift($parts);
        }
        // プロトコルリストにない場合はユーザーエージェント判定する
        else
        {
            $this->protocol = (true === Useragent::isMobile() ? self::PROTOCOL_SP : self::PROTOCOL_PC);
        }

        // ルーティング要素が１つしか無い場合はデフォルトでindexをつける
        if (1 === count($parts))
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
     *
     * @param string|null $suffix クラス名接尾辞
     * @return string
     */
    public function toClassPath(string $suffix = ''): string
    {
        // パーツをスタックしていく
        $parts = [];
        $parts[] = $this->protocol;
        $parts += $this->documents;

        // 先頭だけを大文字に変換
        foreach ($parts as $ky => $vl)
        {
            $parts[$ky] = ucfirst(strtolower($vl));
        }

        // 文字列化して返却 \Hoge\fuga の様な文字列
        return '\\' . implode('\\', $parts) . $suffix;
    }



    /**
     * リクエストからファイルパスを生成する
     *
     * @return string[]
     */
    public function toUcFirstPaths(): array
    {
        // パーツをスタックしていく
        $parts = array_merge([$this->protocol], $this->documents, [$this->action]);
        // 先頭だけを大文字に変換
        foreach ($parts as $ky => $vl)
        {
            $parts[$ky] = ucfirst(strtolower($vl));
        }
        return $parts;
    }



    /**
     * {@inheritDoc}
     */
    protected function configureKey(): string
    {
        return 'router';
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
}
