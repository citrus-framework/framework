<?php
/**
 * @copyright   Copyright 2018, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Mail\Imap;

use stdClass;

class Box
{
    /** @var string 最終変更日 (現在の日付時刻) */
    public $timestamp;

    /** @var string 最終変更日 (現在の日付時刻) */
    public $date;

    /** @var string ドライバ */
    public $driver;

    /** @var string メールボックスの名前 */
    public $mailbox;

    /** @var int メッセージ数 */
    public $quantity;

    /** @var int 最近のメッセージの数 */
    public $recent;

    /** @var int 未読のメッセージの数 */
    public $unread;

    /** @var int 削除されたメッセージの数 */
    public $deleted;

    /** @var int メールボックスのサイズ */
    public $size;



    /**
     * CitrusMailImapBox constructor.
     *
     * @param stdClass $mailboxmsginfo imap_mailboxmsginfoで取得できるオブジェクト
     */
    public function __construct(stdClass $mailboxmsginfo)
    {
        $this->timestamp = strtotime($mailboxmsginfo->Date);
        $this->date = date('Y-m-d H:i:s', $this->timestamp);
        $this->driver = $mailboxmsginfo->Driver;
        $this->mailbox = $mailboxmsginfo->Mailbox;
        $this->quantity = $mailboxmsginfo->Nmsgs;
        $this->recent = $mailboxmsginfo->Recent;
        $this->unread = (isset($mailboxmsginfo->Unread) ? $mailboxmsginfo->Unread : null);
        $this->deleted = (isset($mailboxmsginfo->Deleted) ? $mailboxmsginfo->Deleted : null);
        $this->size = (isset($mailboxmsginfo->Size) ? $mailboxmsginfo->Size : null);
    }



    /**
     * フォルダサイズをKBで取得
     *
     * @return float|int
     */
    public function sizeKB()
    {
        return $this->size / 1024;
    }



    /**
     * フォルダサイズをで取得
     *
     * @return float|int
     */
    public function sizeMB()
    {
        return $this->sizeKB() / 1024;
    }
}
