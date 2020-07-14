<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Mail;

use Citrus\Mail\Header\Item;
use Citrus\Mail\Imap\Account;
use Citrus\Mail\Imap\Box;
use Citrus\Mail\Imap\Quota;
use Citrus\Mail\Search\Condition;

/**
 * IMAP処理
 */
class Imap
{
    /** サーバーパスフォーマット */
    const FORMAT_SERVER_PATH = '{%s}%s';

    /** @var resource IMAP */
    public $imap_handle;

    /** @var Account アカウント */
    public $account;



    /**
     * CitrusMailImap constructor.
     *
     * @param Account $account アカウント情報
     */
    public function __construct(Account $account)
    {
        $this->account = $account;
    }



    /**
     * destructor.
     */
    public function __destruct()
    {
        $this->close();
    }



    /**
     * 指定フォルダのハンドルのオープン
     *
     * @param string $folder_name 指定フォルダ
     * @return bool|resource
     */
    public function open(string $folder_name = '')
    {
        // メールボックス名
        $mailbox_name = $this->callMailBox($folder_name);
        // メールボックスが UTF-8 だった場合は UTF7-IMAP に変更
        $mailbox_name = self::encodeImap($mailbox_name);

        // resource IMAP のオープン
        if (is_null($this->imap_handle) === true)
        {
            $this->imap_handle = imap_open(
                $mailbox_name,
                $this->account->username,
                $this->account->password
            );
        }
        // すでにオープンされている場合は、再度オープンする(フォルダ指定されている場合だけ)
        else if (empty($folder_name) === false)
        {
            @imap_reopen(
                $this->imap_handle,
                $mailbox_name
            );
            // エラースタックをクリアする
            imap_errors();
        }
        return $this->imap_handle;
    }



    /**
     * ハンドルのクローズ
     */
    public function close()
    {
        if (is_null($this->imap_handle) === false)
        {
            imap_close($this->imap_handle);
            $this->imap_handle = null;
        }
    }



    /**
     * フォルダリストの取得
     *
     * @param string $folder_name フォルダ
     * @param string $pattern     検索パターン
     * @return string[]
     */
    public function folders(string $folder_name = '', string $pattern = '*')
    {
        $result = [];

        $folders = imap_list(
            $this->open(),
            $this->callMailBox($folder_name),
            $pattern
        );
        foreach ($folders as $folder)
        {
            $result[] = self::decodeImap($folder);
        }

        return $result;
    }



    /**
     * メールボックスクォータの取得
     *
     * @return Quota
     */
    public function quota()
    {
        return new Quota(imap_get_quotaroot($this->open(), 'INBOX'));
    }



    /**
     * フォルダ情報の取得
     *
     * @param string $folder_name フォルダ
     * @return Box
     */
    public function folderDetail(string $folder_name = '')
    {
        return new Box(imap_mailboxmsginfo($this->open($folder_name)));
    }



    /**
     * サーバー情報を抜いたフォルダ名の取得
     *
     * @param string $folder_name
     * @return string
     */
    public function folderPureName(string $folder_name)
    {
        return str_replace(
            sprintf('{%s}', $this->account->mail_server),
            '',
            $folder_name
        );
    }



    /**
     * 指定フォルダのメール概要を取得する
     *
     * @param Condition|null $condition 検索条件
     * @return Item[]
     */
    public function mailSummaries(Condition $condition = null)
    {
        // 検索条件
        $condition = $condition ?: (new Condition());

        // メールボックスハンドル
        $handle = $this->open($condition->folder_path);

        // 検索シーケンス
        $sequence = null;
        // 検索オプション
        $options = 0;

        // msgno で検索
        if (empty($condition->msgnos) === false)
        {
            $sequence = implode(',', $condition->msgnos);
        }
        // uid で検索
        else if (empty($condition->uids) === false)
        {
            $sequence = implode(',', $condition->uids);
            $options = FT_UID;
        }
        // 全て取得
        else
        {
            $mailBox = new Box(imap_check($handle));
            // メールが1件だった場合に'1:1'にするとnoticeが発生する
            $sequence = $condition->uid_from;
            $quantity = $mailBox->quantity;

            if ($mailBox->quantity > 1 && $sequence < $quantity)
            {
                $sequence = sprintf('%d:%d', $sequence, $quantity);
            }

            // メールが0件だった場合はメールが存在していないのでスキップ
            if ($quantity === 0)
            {
                return [];
            }
        }

        // メール取得
        $fetches = imap_fetch_overview($handle, (string)$sequence, $options);
        // オブジェクト化
        $results = [];
        foreach ($fetches as $fetch)
        {
            $results[] = new Item($fetch);
        }
        return $results;
    }



    /**
     * メール移動
     *
     * @param Condition $condition      検索条件
     * @param string                    $folder_path_to 移動先フォルダパス
     */
    public function move(Condition $condition, string $folder_path_to)
    {
        // エンコード変更
        $folder_path_to = self::encodeImap($folder_path_to);

        // 条件がない場合は処理しない
        if (empty($condition->uids) === true && empty($condition->msgnos) === true)
        {
            return;
        }

        // メールボックスハンドル
        $handle = $this->open($condition->folder_path);

        // 検索シーケンス
        $sequence = null;
        // 検索オプション
        $options = 0;

        // msgno で検索
        if (empty($condition->msgnos) === false)
        {
            $sequence = implode(',', $condition->msgnos);
        }
        // uid で検索
        else if (empty($condition->uids) === false)
        {
            $sequence = implode(',', $condition->uids);
            $options = CP_UID;
        }
        // 全て移動
        else
        {
            $mailBox = new Box(imap_check($handle));
            $sequence = sprintf('1:%d', $mailBox->quantity);
        }

        // 移動実行
        imap_mail_move($handle, $sequence, $folder_path_to, $options);
        // 移動したメールの削除
        imap_expunge($handle);
    }



    /**
     * フォルダの作成
     *
     * @param string $folder_path 作成フォルダパス
     */
    public function createFolder(string $folder_path)
    {
        // 完全なメールフォルダパス
        $mailbox_ref = $this->callMailBox($folder_path);

        // フォルダ購読
        $this->subscribe($folder_path);

        // メールフォルダの作成
        imap_create($this->open(), self::encodeImap($mailbox_ref));
    }



    /**
     * フォルダの削除
     *
     * @param string $folder_path 削除フォルダパス
     */
    public function removeFolder(string $folder_path)
    {
        // 完全なメールフォルダパス
        $mailbox_ref = $this->callMailBox($folder_path);

        // フォルダ購読しているか
        if ($this->is_subscribe($folder_path) === true)
        {
            // フォルダ購読解除
            $this->unsubscribe($folder_path);
        }

        // メールフォルダの作成
        imap_deletemailbox($this->open(), self::encodeImap($mailbox_ref));
    }



    /**
     * フォルダー移動
     *
     * @param string $folder_path_from 移動元フォルダパス
     * @param string $folder_path_to   移動先フォルダパス
     * @param bool   $is_subscribe     購読情報も移動する
     */
    public function rename(string $folder_path_from, string $folder_path_to, bool $is_subscribe = false)
    {
        // エンコード変更
        $encoded_folder_path_from = self::encodeImap($folder_path_from);
        $encoded_folder_path_to = self::encodeImap($folder_path_to);

        // サーバー修飾子の追加
        $encoded_folder_path_from = sprintf(self::FORMAT_SERVER_PATH, $this->account->mail_server, $encoded_folder_path_from);
        $encoded_folder_path_to = sprintf(self::FORMAT_SERVER_PATH, $this->account->mail_server, $encoded_folder_path_to);

        // 購読情報の移動
        if (true === $is_subscribe)
        {
            // フォルダ購読しているか
            if (true === $this->is_subscribe($folder_path_from))
            {
                // フォルダ購読解除
                $this->unsubscribe($folder_path_from);
            }
            else
            {
                // フォルダ購読していないのに購読解除しようとした場合は、購読処理しない
                $is_subscribe = false;
            }
        }

        // フォルダ移動
        imap_rename($this->open(), $encoded_folder_path_from, $encoded_folder_path_to);

        // フォルダ購読
        if ($is_subscribe === true)
        {
            $this->subscribe($folder_path_to);
        }
    }



    /**
     * フォルダ購読をしているか
     *
     * @param string $folder_path 購読確認するフォルダパス
     * @return bool true:購読している,false:購読していない
     */
    public function is_subscribe(string $folder_path)
    {
        return is_array(imap_listsubscribed($this->open(), $this->callMailBox(), self::encodeImap($folder_path)));
    }



    /**
     * フォルダの購読
     *
     * @param string $folder_path 購読するフォルダパス
     * @return bool
     */
    public function subscribe(string $folder_path)
    {
        return imap_subscribe($this->open(),
            sprintf(self::FORMAT_SERVER_PATH, $this->account->mail_server, self::encodeImap($folder_path))
        );
    }



    /**
     * フォルダの購読解除
     *
     * @param string $folder_path 購読解除するフォルダパス
     * @return bool
     */
    public function unsubscribe(string $folder_path)
    {
        return imap_unsubscribe($this->open(),
            sprintf(self::FORMAT_SERVER_PATH, $this->account->mail_server, self::encodeImap($folder_path))
        );
    }



    /**
     * メールサーバー文字列の取得
     *
     * @param string $folder_name 指定フォルダ名
     * @return string
     */
    private function callMailBox(string $folder_name = '')
    {
        if ($folder_name === '')
        {
            return sprintf('{%s}', $this->account->mail_server);
        }

        $decoration_mailbox_ref = $this->callMailBox();
        return sprintf('%s%s',
            $decoration_mailbox_ref,
            str_replace($decoration_mailbox_ref, '', $folder_name)
        );
    }



    /**
     * IMAP用文字列にエンコードする
     *
     * @param string $context
     * @return string
     */
    private static function encodeImap(string $context)
    {
        return mb_convert_encoding($context, 'UTF7-IMAP', 'UTF-8');
    }



    /**
     * IMAP用文字列からデコードする
     *
     * @param string $context
     * @return string
     */
    private static function decodeImap(string $context)
    {
        return mb_convert_encoding($context, 'UTF-8', 'UTF7-IMAP');
    }
}
