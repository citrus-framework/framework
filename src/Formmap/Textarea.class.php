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
 * テキストエリア
 */
class Textarea extends Element
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
            'type' => 'text',
            'id' => $this->callPrefixedId(),
            'name' => $this->callPrefixedId(),
            'class' => $this->class,
            'style' => $this->style,
            'size' => $this->size,
            'maxlength' => $this->max,
            'placeholder' => $this->placeholder,
        ];
        $elements = self::appendOption($elements, $appends);

        return self::generateTag('textarea', $elements, [ NVL::coalesceNull($this->value, $this->callValue(), $this->callDefault()) ]);
    }
}
