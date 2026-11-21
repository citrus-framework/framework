<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2026, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Authentication;

use Citrus\CitrusException;

/**
 * ID・パスワードだけの認証アイテムを、テーブルのユーザーに置き換える
 */
class CredentialResolver
{
    /**
     * @param UserLookup $lookup ユーザー取得
     */
    public function __construct(
        private UserLookup $lookup,
    ) {
    }

    /**
     * トークンが無く、ID・パスワードがある場合はデータ取得して返す。そうでなければそのまま返す
     * @param AuthItem $item 認証アイテム
     * @return AuthItem
     * @throws AuthenticationException
     * @throws CitrusException
     */
    public function resolve(AuthItem $item): AuthItem
    {
        if ($item->token === null and $item->user_id !== null and $item->password !== null)
        {
            // 対象ユーザーがいるか？
            $result = $this->lookup->find($item->user_id);

            // いなければ認証失敗
            AuthenticationException::exceptionIf(
                is_null($result),
                sprintf(
                    '存在しないユーザーのログイン試行です(%s : %s)',
                    $item->user_id,
                    $item->password,
                ),
            );
            // パスワード照合
            AuthenticationException::exceptionElse(
                password_verify($item->password, $result->password),
                sprintf(
                    'パスワード照合に失敗しました(%s : %s)',
                    $item->user_id,
                    $item->password,
                ),
            );
            return $result;
        }

        return $item;
    }
}
