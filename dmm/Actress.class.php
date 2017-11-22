<?php
/**
 * Actress.class.php.
 *
 *
 * PHP version 7
 *
 * @copyright   Copyright 2017, Citrus/besidesplus All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @package     Citrus
 * @subpackage  Dmm
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Dmm;


class CitrusDmmActress
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