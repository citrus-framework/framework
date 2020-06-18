<?php
/**
 * @copyright   Copyright 2018, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Mail\Imap;

class Account
{
    /** @var string mail server */
    public $mail_server;

    /** @var string username */
    public $username;

    /** @var string password */
    public $password;



    /**
     * CitrusMailImap constructor.
     *
     * @param string $mail_server メールサーバー
     * @param string $username    メールユーザー
     * @param string $password    メールパスワード
     */
    public function __construct(string $mail_server, string $username, string $password)
    {
        $this->mail_server = $mail_server;
        $this->username = $username;
        $this->password = $password;
    }
}
