<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Formmap;

use Citrus\NVL;

/**
 * セレクトボックス
 */
class Select extends Element
{
    /**
     * to string
     *
     * @param array $appends
     * @return string
     */
    public function toString(array $appends = [])
    {
        $elements = [
            'type' => 'select',
            'id' => $this->callPrefixedId(),
            'name' => $this->callPrefixedId(),
            'value' => NVL::coalesceNull($this->value, $this->callValue(), $this->callDefault()),
            'default' => $this->callDefault(),
            'class' => $this->class,
            'style' => $this->style,
            'accesskey' => $this->accesskey,
        ];
        $elements = self::appendOption($elements, $appends);

        return self::generateTag('select', $elements, $this->options);
    }
}
