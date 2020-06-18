<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Xhr;

use Citrus\Document\Pager;

/**
 * @deprecated
 */
class Result
{
    /** @var bool result */
    public $result = false;

    /** @var array result object */
    public $items = [];

    /** @var Pager pager  */
    public $pager = null;



    /**
     * constructor.
     *
     * @param array $items
     */
    public function __construct(array $items = [])
    {
        if ($items == [false])
        {
            $this->result = false;
        }
        else if (empty($items) === false || is_array($items) === true)
        {
            $this->result = true;
        }
        $this->items = $items;
    }
}
