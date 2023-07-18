<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
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
     * @param AuthItem $item
     * @return bool true:認証成功, false:認証失敗
     */
    abstract public function authorize(AuthItem $item): bool;


    /**
     * 認証解除処理
     *
     * @return bool true:処理成功
     */
    abstract public function deAuthorize(): bool;



    /**
     * 認証のチェック
     * 認証できていれば期間の延長
     *
     * @param AuthItem|null $item
     * @return bool true:チェック成功, false:チェック失敗
     */
    abstract public function isAuthenticated(AuthItem|null $item = null): bool;
}
