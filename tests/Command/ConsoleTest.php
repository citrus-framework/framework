<?php
/**
 * @copyright   Copyright 2019, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

use Citrus\Command\Console;
use PHPUnit\Framework\TestCase;

/**
 * コマンドコンソールのテスト
 */
class ConsoleTest extends TestCase
{
    /**
     * @test
     */
    public function 文字出力で例外が発生しない()
    {
        $command = new TestCommand();

        $command->write('TEST', true);
        $command->writeln('TEST');
        $command->format('TES%s', 'T');
        $command->success('TEST');
        $command->error('TEST');
    }
}

/**
 * トレイト反映用のテストコマンド
 */
class TestCommand
{
    use Console;
}
