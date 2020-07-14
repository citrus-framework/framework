<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Session;

use Citrus\CitrusException;

/**
 * セッション例外
 */
class SessionException extends CitrusException
{
    /**
     * {@inheritDoc}
     *
     * @throws SessionException
     */
    public static function exceptionIf($expr, string $message): void
    {
        parent::exceptionIf($expr, $message);
    }



    /**
     * {@inheritDoc}
     *
     * @throws SessionException
     */
    public static function exceptionElse($expr, string $message): void
    {
        parent::exceptionElse($expr, $message);
    }
}
