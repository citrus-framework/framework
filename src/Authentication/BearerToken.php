<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2026, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Authentication;

/**
 * Authorization ヘッダの Bearer のトークン
 */
class BearerToken
{
    /**
     * リクエストヘッダから Bearer のトークンを取り出す
     * ヘッダ名は大文字・小文字を区別しない（HTTP/2 などでは小文字で届く）
     * @param array<int|string, string> $headers リクエストヘッダ
     * @return string トークン。ヘッダが無い、またはトークンが無いときは空文字
     */
    public static function fromHeaders(array $headers): string
    {
        foreach ($headers as $name => $value)
        {
            // 数字だけのヘッダ名は、配列では整数のキーになる
            if (strcasecmp((string)$name, 'Authorization') === 0)
            {
                return explode(' ', $value)[1] ?? '';
            }
        }
        return '';
    }
}
