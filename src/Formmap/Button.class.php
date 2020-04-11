<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Formmap;

/**
 * ボタン
 */
class Button extends Element
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
            'type' => 'button',
            'id' => $this->callPrefixedId(),
            'name' => $this->callPrefixedId(),
            'value' => $this->name,
            'class' => $this->class,
            'style' => $this->style,
            'accesskey' => $this->accesskey,
        ];
        $elements = self::appendOption($elements, $appends);

        return self::generateTag('button', $elements, $this->name);
    }
}
