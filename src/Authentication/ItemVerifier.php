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
 * 認証アイテムの有効性の検証
 */
class ItemVerifier
{
    /**
     * ユーザーID・トークン・認証期間があり、認証期間が切れていないことを検証する
     * @param AuthItem $item 認証アイテム
     * @return void
     * @throws AuthenticationException
     * @throws CitrusException
     */
    public function verify(AuthItem $item): void
    {
        // トークンの値はログに出さず、あるかどうかだけを書く
        $token_state = $item->token === null ? 'なし' : 'あり';

        // ユーザーIDとトークン、認証期間があるか
        AuthenticationException::exceptionIf(
            $item->user_id === null or $item->token === null or $item->expired_at === null,
            sprintf(
                'ユーザIDが無い(user_id=%s)、もしくはトークンが無い(token=%s)、もしくはタイムアウト(expired_at=%s)',
                $item->user_id,
                $token_state,
                $item->expired_at
            ),
        );

        // すでに認証期間が切れている
        $expired_ts = strtotime($item->expired_at);
        $now_ts = time();
        AuthenticationException::exceptionIf(
            $expired_ts < $now_ts,
            sprintf(
                'タイムアウト(%s) < 現在時間(%s)',
                $expired_ts,
                $now_ts
            ),
        );
    }
}
