<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Cache;

/**
 * キャッシュエンジンインターフェース
 */
interface Engine
{
    /**
     * 値の取得
     *
     * @param string $key
     * @return object|array|string|float|int|bool|null
     */
    public function call(string $key): object|array|string|float|int|bool|null;

    /**
     * 値の設定
     *
     * @param string $key    キー
     * @param object|array|string|float|int|bool  $value  値
     * @param int    $expire 期限切れまでの時間
     */
    public function bind(string $key, object|array|string|float|int|bool $value, int $expire = 0): void;

    /**
     * 値の存在確認
     *
     * @param string $key
     * @return bool
     */
    public function exists(string $key): bool;

    /**
     * 値の取得
     * 存在しない場合は値の設定ロジックを実行し、返却する
     *
     * @param string   $key           キー
     * @param callable $valueFunction 無名関数
     * @param int      $expire        期限切れまでの時間
     * @return object|array|string|float|int|bool
     */
    public function callWithBind(
        string $key,
        callable $valueFunction,
        int $expire = 0
    ): object|array|string|float|int|bool;
}
