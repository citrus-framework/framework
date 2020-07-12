<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Process;

/**
 * 処理進捗
 */
class Progress
{
    /** @var int total count */
    public $total_count = 0;

    /** @var int regist count */
    public $regist_count = 0;

    /** @var int modify count */
    public $modify_count = 0;



    /**
     * 登録・更新の情報
     *
     * @param int $total
     * @param int $regist
     * @param int $modify
     * @return Progress
     */
    public static function generateEntry(int $total = 0, int $regist = 0, int $modify = 0): Progress
    {
        $progress = new static();

        $progress->total_count = $total;
        $progress->regist_count = $regist;
        $progress->modify_count = $modify;

        return $progress;
    }
}
