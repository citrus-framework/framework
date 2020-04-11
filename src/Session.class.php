<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus;

use Citrus\Session\Item;
use Citrus\Variable\Structs;

/**
 * セッション処理
 */
class Session
{
    use Structs;

    /** @var Item $_SESSION values 'data' -> 'element' */
    public static $session;

    /** @var Item $_GET values */
    public static $getdata;

    /** @var Item $_POST values */
    public static $postdata;

    /** @var Item $_FILES values */
    public static $filedata;

    /** @var Item $_SERVER values */
    public static $server;

    /** @var Item $_POST + $_GET values */
    public static $request;

    /** @var \Citrus\Router\Item routing element */
    public static $router;

    /** @var string session id */
    public static $sessionId;



    /**
     * session run
     *
     * @deprecated
     */
    public static function run()
    {
        self::factory(true);
    }

    /**
     * session run page
     */
    public static function page()
    {
        self::factory(true);
    }

    /**
     * session run part
     */
    public static function part()
    {
        self::factory(false);
    }

    /**
     * session factory method
     *
     * @param bool $use_ticket
     */
    public static function factory(bool $use_ticket = true)
    {
        session_name('CITRUSSESSID');

        $use_ticket = false;
        if ($use_ticket === true)
        {
            // get ticket
            $citrus_ticket_key = NVL::ArrayVL($_REQUEST, 'ctk', '');
            if (empty($citrus_ticket_key) === true)
            {
                $citrus_ticket_key = md5(uniqid(rand()));
            }
            session_id($citrus_ticket_key);
        }

        // connect session
        session_start();

        // save old session data
        self::$session = new Item($_SESSION['data'] ?? null);
        self::$getdata = new Item($_GET);
        self::$postdata = new Item($_POST);
        self::$filedata = new Item($_FILES);
        self::$server = new Item($_SERVER);
        self::$request = new Item($_REQUEST);
        self::$router = Router::sharedInstance()->factory($_REQUEST);

        session_regenerate_id(true);
        self::$sessionId = session_id();
    }



    /**
     * clear
     */
    public static function clear()
    {
        self::$session = null;
        session_unset();
    }



    /**
     * commiter
     */
    public static function commit()
    {
        $_SESSION['data'] = self::$session;
        session_commit();
    }



    /**
     * status
     */
    public static function status()
    {
        return session_status();
    }



    /**
     * destroy
     */
    public static function destroy()
    {
        return session_destroy();
    }
}
