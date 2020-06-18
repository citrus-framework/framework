<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
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
     * @param mixed $key
     * @return mixed
     */
    public function call($key);



    /**
     * 値の設定
     *
     * @param string $key    キー
     * @param mixed  $value  値
     * @param int    $expire 期限切れまでの時間
     */
    public function bind(string $key, $value, int $expire = 0);



    /**
     * 値の存在確認
     *
     * @param mixed $key
     * @return bool
     */
    public function exists($key): bool;



    /**
     * 値の取得
     * 存在しない場合は値の設定ロジックを実行し、返却する
     *
     * @param string   $key           キー
     * @param callable $valueFunction 無名関数
     * @param int      $expire        期限切れまでの時間
     * @return mixed
     */
    public function callWithBind($key, callable $valueFunction, int $expire = 0);
}
