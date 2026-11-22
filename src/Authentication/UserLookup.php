<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2026, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Authentication;

use Citrus\Authentication;
use Citrus\Query\Builder;
use Citrus\Query\Executor;

/**
 * 認証テーブルからのユーザー取得
 */
class UserLookup
{
    /**
     * @param Executor $executor クエリ実行
     */
    public function __construct(
        #[\SensitiveParameter] private Executor $executor,
    ) {
    }

    /**
     * ユーザーIDからユーザーを取得する
     * @param string|null $user_id ユーザーID
     * @return AuthItem|null 該当するユーザー。いなければ null
     */
    public function find(string|null $user_id): AuthItem|null
    {
        return $this->executor
            ->build(
                (new Builder(Authentication::$AUTHORIZE_TABLE_NAME))->selectQuery()
                    ->whereEqual('user_id', $user_id)
                    ->resultClass(AuthItem::class)
            )
            ->fetch()
            ->one();
    }

    /**
     * ユーザーIDとパスワードが一致するユーザーを取得する
     * @param string $user_id  ユーザーID
     * @param string $password パスワード
     * @return AuthItem|null 一致するユーザー。いない、またはパスワードが一致しなければ null
     */
    public function findVerified(string $user_id, #[\SensitiveParameter] string $password): AuthItem|null
    {
        $result = $this->find($user_id);

        // いなければ、またはパスワード照合に失敗すれば該当なし
        if ($result === null or password_verify($password, $result->password) === false)
        {
            return null;
        }

        return $result;
    }
}
