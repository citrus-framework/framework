<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusGeneration. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Test\Sample\Command\Sample;

use Citrus\Configure\ConfigureException;
use Citrus\Console;

/**
 * Sampleデータコマンド
 */
class SampleDataCommand extends Console
{
    /** @var array command options */
    protected array $options = [
    ];



    /**
     * {@inheritDoc}
     *
     * @throws ConfigureException
     */
    public function execute(): void
    {
        parent::execute();
        echo 'execute!';
    }
}
