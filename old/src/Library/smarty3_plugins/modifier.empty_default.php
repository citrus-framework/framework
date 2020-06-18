<?php
/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */


/**
 * empty評価される場合のデフォルト表示
 *
 * @param mixed  $value   評価変数
 * @param string $default デフォルト内容
 * @return string
 */
function smarty_modifier_empty_default($value, $default = '')
{
    if (empty($value) !== true)
    {
        return $value;
    }

    return $default;
}
