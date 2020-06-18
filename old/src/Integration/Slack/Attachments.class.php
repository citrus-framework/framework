<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Integration\Slack;

use Citrus\Variable\Structs;

class Attachments
{
    use Structs;

    /** @var string fallback */
    public $fallback = '';

    /** @var string color */
    public $color = '';

    /** @var string pretext */
    public $pretext = '';

    /** @var string author_name */
    public $author_name = '';

    /** @var string author_link */
    public $author_link = '';

    /** @var string author_icon */
    public $author_icon = '';

    /** @var string title */
    public $title = '';

    /** @var string title_link */
    public $title_link = '';

    /** @var string text */
    public $text = '';

    /** @var array fields */
    public $fields = [];

    /** @var string image_url */
    public $image_url = '';

    /** @var string thumb_url */
    public $thumb_url = '';

    /** @var string footer */
    public $footer = 'Citrus Slack';

    /** @var string footer_icon */
    public $footer_icon = 'https://platform.slack-edge.com/img/default_application_icon.png';

    /** @var int ts */
    public $ts = 0;
}
