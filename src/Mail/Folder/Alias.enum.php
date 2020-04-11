<?php
/**
 * @copyright   Copyright 2018, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Mail\Folder;

class Alias
{
    /** 受信トレイ */
    const INBOX = 'INBOX';

    /** アーカイブ */
    const ARCHIVE = 'Archive';

    /** 下書き */
    const DRAFTS = 'Drafts';

    /** 迷惑メール */
    const JUNK = 'Junk';

    /** 送信トレイ */
    const SENT = 'Sent';

    /** ゴミ箱 */
    const TRASH = 'Trash';

    /** @var array フォルダ別名一覧 */
    public static $TABLES = [
        self::INBOX => '受信トレイ',
        self::ARCHIVE => 'アーカイブ',
        self::DRAFTS => '下書き',
        self::JUNK => '迷惑メール',
        self::SENT => '送信トレイ',
        self::TRASH => 'ゴミ箱',
    ];



    /**
     * フォルダ別名の取得
     *
     * @param string $folder_name
     * @return mixed|string
     */
    public static function alias(string $folder_name)
    {
        return (isset(self::$TABLES[$folder_name]) === true ? self::$TABLES[$folder_name] : $folder_name);
    }
}
