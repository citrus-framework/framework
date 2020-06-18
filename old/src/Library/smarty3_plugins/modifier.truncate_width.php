<?php
/**
 * @copyright   Copyright 2018, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */



/**
 * マルチバイト文字列幅を考慮したtruncate
 *
 * @param string $context 対象文字列
 * @param int    $length  縮める文字幅
 * @param string $etc     付加する文字
 * @return string
 */
function smarty_modifier_truncate_width(string $context, int $length = 80, string $etc = '...')
{
    // 対象文字列長
    $context_length = mb_strwidth($context);

    // 文字列長が達していない場合
    if ($context_length <= $length)
    {
        return $context;
    }

    // 付加文字列長
    $etc_length = mb_strwidth($etc);

    // 指定文字列
    return sprintf('%s%s', mb_strimwidth($context, 0, ($length - $etc_length)), $etc);
}
