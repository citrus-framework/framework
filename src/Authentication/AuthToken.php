<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Authentication;

/**
 * トークン生成インターフェース
 */
interface AuthToken
{
    /**
     * トークン生成処理
     * @param array|null $options
     * @return string
     */
    public function token(array|null $options = []): string;

    /**
     * トークン検証処理
     * @param string     $token
     * @param array|null $options
     * @return bool
     */
    public function verify(string $token, array|null $options = []): bool;
}
