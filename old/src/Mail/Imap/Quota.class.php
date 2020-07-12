<?php
/**
 * @copyright   Copyright 2018, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Mail\Imap;

class Quota
{
    /** @var int 利用サイズ */
    public $usage;

    /** @var int 最大サイズ */
    public $limit;



    /**
     * CitrusMailImapQuota constructor.
     *
     * @param array $quotaroot imap_get_quotarootで取得できる配列
     */
    public function __construct(array $quotaroot)
    {
        $this->usage = $quotaroot['usage'];
        $this->limit = $quotaroot['limit'];
    }
}
