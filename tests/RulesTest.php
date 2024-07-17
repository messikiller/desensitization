<?php

namespace Leoboy\Desensitization\Tests;

use Leoboy\Desensitization\Desensitizer;
use Leoboy\Desensitization\Rules\Cut;
use Leoboy\Desensitization\Rules\Mask;
use Leoboy\Desensitization\Rules\Mix;
use Leoboy\Desensitization\Rules\None;
use Leoboy\Desensitization\Rules\Replace;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RulesTest extends TestCase
{
    #[DataProvider('cutDataProvider')]
    public function testCut(Cut $cutRule, $input, $expectedOutput): void
    {
        $desensitizer = new Desensitizer();
        $this->assertSame(
            $expectedOutput,
            $desensitizer->invoke($input, $cutRule)
        );
    }

    #[DataProvider('maskDataProvider')]
    public function testMask(Mask $maskRule, $input, $expectedOutput): void
    {
        $desensitizer = new Desensitizer();
        $this->assertSame(
            $expectedOutput,
            $desensitizer->invoke($input, $maskRule)
        );
    }

    public function testReplace(): void
    {
        $desensitizer = new Desensitizer();
        $this->assertSame(
            '-',
            $desensitizer->invoke('abc123', Replace::create()->use('-'))
        );
    }

    public function testNone(): void
    {
        $desensitizer = new Desensitizer();
        $this->assertSame(
            'abc123',
            $desensitizer->invoke('abc123', None::create())
        );
    }

    public function testMix(): void
    {
        $desensitizer = new Desensitizer();
        $this->assertSame(
            'i***L',
            $desensitizer->invoke(
                'ThisIsLongSentenceWords',
                Mix::create()
                    ->append(Cut::create()->start(2)->length(5))
                    ->append(Mask::create()->use('*')->padding(1)->repeat(3))
            )
        );
    }

    public static function cutDataProvider(): array
    {
        return [
            [Cut::create()->start(1)->length(2), 'abc123', 'bc'],
            [Cut::create()->start(1)->length(2), 'a', ''],
            [Cut::create()->start(0)->length(2), 'a', 'a'],
            [Cut::create()->start(0)->length(2), 'abc123', 'ab'],
            [Cut::create()->start(-3)->length(2), 'abc123', '12'],
            [Cut::create()->start(-1)->length(2), 'abc123', '3'],
            [Cut::create()->start(1)->length(2), '这是一多多字节文字', '是一'],
        ];
    }

    public static function maskDataProvider(): array
    {
        return [
            [Mask::create()->use('*')->padding(2)->repeat(3), 'abc123', 'ab***23'],
            [Mask::create()->use('*')->padding(2)->repeat(3), 'abc1', 'ab***c1'],
            [Mask::create()->use('*')->padding(2)->repeat(3), 'abc', '***'],
            [Mask::create()->use('*')->padding(1)->repeat(3), 'abc123', 'a***3'],
            [Mask::create()->use('*')->padding(0)->repeat(3), 'abc123', '***'],
            [Mask::create()->use('*')->padding(1)->repeat(1), 'abc123', 'a*3'],
            [Mask::create()->use('-')->padding(1)->repeat(3), 'abc123', 'a---3'],
            [Mask::create()->use('*')->padding(2)->repeat(3), '这是一段多字节文字', '这是***文字'],
        ];
    }
}
