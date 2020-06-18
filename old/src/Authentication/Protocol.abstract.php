<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Authentication;

/**
 * 認証プロトコル
 */
abstract class Protocol
{
    /**
     * 認証処理
     *
     * @param Item $item
     * @return bool ture:認証成功, false:認証失敗
     */
    abstract public function authorize(Item $item): bool;


    /**
     * 認証解除処理
     *
     * @return bool ture:認証成功, false:認証失敗
     */
    abstract public function deauthorize(): bool;



    /**
     * 認証のチェック
     * 認証できていれば期間の延長
     *
     * @param Item|null $item
     * @return bool true:チェック成功, false:チェック失敗
     */
    abstract public function isAuthenticated(Item $item = null): bool;
}
