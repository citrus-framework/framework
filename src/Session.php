<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus;

use Citrus\Session\Item;
use Citrus\Variable\Strings;
use Citrus\Variable\Structs;

/**
 * セッション処理
 */
class Session
{
    use Structs;

    /** @var Item $_SESSION values 'data' -> 'element' */
    public static Item|null $session;

    /** @var Item $_SERVER values */
    public static Item $server;

    /** @var Item $_REQUEST values */
    public static Item $request;

    /** @var string session id */
    public static string $sessionId;



    /**
     * session run page
     */
    public static function page(): void
    {
        self::factory(true);
    }



    /**
     * session run part
     */
    public static function part(): void
    {
        self::factory(false);
    }



    /**
     * session factory method
     *
     * @param bool $use_ticket
     */
    public static function factory(bool $use_ticket = false): void
    {
        session_name('CITRUSSESSID');

        if (true === $use_ticket)
        {
            // get ticket
            $citrus_ticket_key = ($_REQUEST['ctk'] ?? '');
            if (true === Strings::isEmpty($citrus_ticket_key))
            {
                $citrus_ticket_key = md5(uniqid((string)rand()));
            }
            session_id($citrus_ticket_key);
        }

        // connect session
        session_start();

        // save old session data
        self::$session = new Item($_SESSION['data'] ?? null);
        self::$server = new Item($_SERVER);
        self::$request = new Item($_REQUEST);

        session_regenerate_id(true);
        self::$sessionId = session_id();
    }



    /**
     * clear
     */
    public static function clear(): void
    {
        self::$session = null;
        session_unset();
    }



    /**
     * commit
     */
    public static function commit(): void
    {
        $_SESSION['data'] = self::$session;
        session_commit();
    }



    /**
     * status
     *
     * @return int
     */
    public static function status(): int
    {
        return session_status();
    }



    /**
     * destroy
     *
     * @return bool
     */
    public static function destroy(): bool
    {
        return session_destroy();
    }
}
