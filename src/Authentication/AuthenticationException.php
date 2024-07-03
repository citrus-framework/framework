<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitronIssue All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Authentication;

use Citrus\CitrusException;

/**
 * 認証用例外
 */
class AuthenticationException extends CitrusException
{
    /**
     * {@inheritDoc}
     * @throws AuthenticationException
     */
    public static function exceptionIf($expr, string $message): void
    {
        parent::exceptionIf($expr, $message);
    }

    /**
     * {@inheritDoc}
     * @throws AuthenticationException
     */
    public static function exceptionElse($expr, string $message): void
    {
        parent::exceptionElse($expr, $message);
    }
}
