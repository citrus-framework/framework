<?php
/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus;

class Citrus
{
    /** @var string */
    public static $JAVASCRIPT_FACES;

    /** @var string */
    public static $JAVASCRIPT_EXTENDS;

    /** @var bool */
    public static $IS_INITIALIZED = false;

    /** @var int */
    public static $TIMESTAMP_INT;

    /** @var string YmdHis */
    public static $TIMESTAMP_CHAR14;

    /** @var string Y-m-d H:i:s */
    public static $TIMESTAMP_FORMAT;

    /** @var string Ymd */
    public static $DATE_CHAR8;

    /** @var string Y-m-d */
    public static $DATE_FORMAT;



    /**
     * フレームワーク初期化
     */
    public static function initialize()
    {
        // is initialized
        if (true === self::$IS_INITIALIZED)
        {
            return;
        }

        $base_path = dirname(__FILE__);

        // static files
        self::$JAVASCRIPT_FACES = $base_path . '/Javascript/Faces.js';
        self::$JAVASCRIPT_EXTENDS = $base_path . '/Javascript/Extends.js';

        // timestamp
        self::$TIMESTAMP_INT = $_SERVER['REQUEST_TIME'];
        self::$TIMESTAMP_CHAR14 = date('YmdHis', self::$TIMESTAMP_INT);
        self::$TIMESTAMP_FORMAT = date('Y-m-d H:i:s', self::$TIMESTAMP_INT);
        // date
        self::$DATE_CHAR8 = date('Ymd', self::$TIMESTAMP_INT);
        self::$DATE_FORMAT = date('Y-m-d', self::$TIMESTAMP_INT);

        // initialized
        self::$IS_INITIALIZED = true;
    }
}
