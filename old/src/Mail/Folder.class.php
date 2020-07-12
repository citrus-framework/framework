<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2018, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Mail;

/**
 * メールフォルダー
 */
class Folder
{
    /**
     * サーバー情報を抜いたピュアなフォルダパスの取得
     *
     * @param string $folder_path フォルダパス
     * @return string
     */
    public static function purePath(string $folder_path)
    {
        return preg_replace_callback('/{.*\}(.*)/', function ($matches) {
            return $matches[1];
        }, $folder_path);
    }



    /**
     * パス階層の配列の取得
     *
     * @param string $folder_path フォルダパス
     * @return string[]
     */
    public static function paths(string $folder_path)
    {
        $pure_folder_path = self::purePath($folder_path);
        return explode('.', $pure_folder_path);
    }
}
