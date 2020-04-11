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
 * パスワード
 */
class Password extends Element
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
            'type' => 'password',
            'id' => $this->callPrefixedId(),
            'name' => $this->callPrefixedId(),
            'value' => NVL::coalesceNull($this->value, $this->callValue(), $this->callDefault()),
            'class' => $this->class,
            'style' => $this->style,
            'size' => $this->size,
            'maxlength' => $this->max,
        ];
        $elements = self::appendOption($elements, $appends);

        return self::generateTag('input', $elements);
    }
}
