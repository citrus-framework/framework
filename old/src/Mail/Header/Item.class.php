<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Mail\Header;

use Citrus\Variable\Structs;
use stdClass;

/**
 * メールヘッダーアイテム
 */
class Item
{
    use Structs;

    /** @var string メッセージの題名(subject) */
    public $subject;

    /** @var string 送信者 */
    public $from;

    /** @var string 受信者 */
    public $to;

    /** @var string 送信日 */
    public $date;

    /** @var string Message-ID */
    public $message_id;

    /** @var string このメッセージ ID への参照です */
    public $references;

    /** @var string このメッセージ ID への返信です */
    public $in_reply_to;

    /** @var int サイズ（バイト数） */
    public $size;

    /** @var int メールボックスにおけるこのメッセージの UID */
    public $uid;

    /** @var int メールボックスにおけるこのメッセージのシーケンス番号 */
    public $msgno;

    /** @var int このメッセージには recent フラグが立てられています */
    public $recent;

    /** @var int フラグが立てられています */
    public $flagged;

    /** @var int 返信済みフラグが立てられています */
    public $answered;

    /** @var int 削除フラグが立てられています */
    public $deleted;

    /** @var int 既読フラグが立てられています */
    public $seen;

    /** @var int 草稿フラグが立てられています */
    public $draft;

    /** @var int 受信日時の UNIX タイムスタンプ */
    public $udate;



    /**
     * CitrusMailHeaderItem constructor.
     *
     * @param stdClass $object imap_fetch_overview から返却されるオブジェクト
     */
    public function __construct(stdClass $object = null)
    {
        if (is_null($object) === false)
        {
            $this->bindObject($object);

            // MIME文字列をデコードをしておく
            $this->subject = self::decodeMIME($this->subject); // メールタイトル
            $this->from = self::decodeMIME($this->from);    // メールFROM
            $this->to = self::decodeMIME($this->to);      // メールTO
        }
    }



    /**
     * メールサイズをKBで取得
     *
     * @return float|int
     */
    public function sizeKB()
    {
        return $this->size / 1024;
    }



    /**
     * メールサイズをで取得
     *
     * @return float|int
     */
    public function sizeMB()
    {
        return $this->sizeKB() / 1024;
    }



    /**
     * 日付文字列の取得
     *
     * @return string
     */
    public function callDate()
    {
        return date('Y-m-d H:i:s', $this->udate);
    }



    /**
     * MIME文字列をデコード
     *
     * @param string $mimeString MIME文字列
     * @return string
     */
    public static function decodeMIME(string $mimeString = null): string
    {
        if (is_null($mimeString) === true)
        {
            return '';
        }

        // MIMEエンコードの間にある空白を除去
        $subject = str_replace('?= =?', '?==?', $mimeString);

        // mimeエンコード文字列とタブ文字を見つけて置換する
        return preg_replace_callback('/(=\?(.*)\?=|\t)/', function ($matches) {
            $match = $matches[0];
            // 内容がタブ文字、もしくは不明mimeの場合
            if ($match === "\t" || stripos($match, '=?x-unknown?B?') === 0)
            {
                return '';
            }
            // 中国語対応
            $match = str_ireplace('=?gb2312?B?', '=?GBK?B?', $match);
            return mb_decode_mimeheader($match);
        }, $subject);
    }
}
