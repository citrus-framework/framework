<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitronIssue All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.besidesplus.net/
 */

namespace Citrus\Controller;

use Citrus\Authentication\AuthItem;
use Citrus\Http\Server\Response;
use Citrus\Variable\Binders;

/**
 * 認証用レスポンス
 */
class AuthResponse extends Response
{
    use Binders;

    /** @var String 認証用トークン */
    public $token;

    /** @var array 認証用アイテム */
    public $user;



    /**
     * token返却用レスポンスの生成
     *
     * @param string $token 認証トークン
     * @return $this
     */
    public static function withToken(string $token): self
    {
        $self = new self();
        $self->token = $token;
        $self->remove([
            'result',
            'items',
            'messages',
            'user',
        ]);
        return $self;
    }



    /**
     * user返却用レスポンスの生成
     *
     * @param AuthItem $item 認証アイテム
     * @return $this
     */
    public static function withItem(AuthItem $item): self
    {
        $self = new self();
        $self->user = [
            'user_id' => $item->user_id,
        ];
        $self->remove([
            'result',
            'items',
            'messages',
            'token',
        ]);
        return $self;
    }
}
