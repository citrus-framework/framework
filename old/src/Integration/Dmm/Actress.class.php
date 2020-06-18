<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Integration\Dmm;

/**
 * 女優情報
 */
class Actress
{
    public $id = null;
    public $name = null;
    public $ruby = null;
    public $bust = null;
    public $cup = null;
    public $waist = null;
    public $hip = null;
    public $height = null;
    public $birthday = null;
    public $blood_type = null;
    public $hobby = null;
    public $prefectures = null;
    public $imageURL = [
        'small' => null,
        'large' => null,
    ];
    public $listURL = [
        'digital' => null,
        'monthly' => null,
        'ppm' => null,
        'mono' => null,
        'rental' => null,
    ];
}
