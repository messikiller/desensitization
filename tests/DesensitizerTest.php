<?php

namespace Leoboy\Desensitization\Tests;

use Leoboy\Desensitization\Desensitizer;
use Leoboy\Desensitization\Helper;
use Leoboy\Desensitization\Rules\Mask;
use Leoboy\Desensitization\Rules\Replace;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class DesensitizerTest extends TestCase
{
    public function testDesensitize(): void
    {
        $desensitizer = new Desensitizer();

        $data = [
            'k1' => 'abcdefg',
            'k2' => [
                'k2_1' => '13034124128',
                'subkey' => [
                    'subStringVal',
                    12345,
                    'subStrKey1' => 'sv1',
                ],
            ],
        ];
        $desensitized = $desensitizer->desensitize($data, [
            'k1' => (fn ($str) => strrev($str)),
            'k2.k2_1' => new Mask(),
            'k2.subkey.*' => new Replace('-'),
        ]);

        $this->assertSame('gfedcba', Helper::arrayGet($desensitized, 'k1'));
        $this->assertSame('1***8', Helper::arrayGet($desensitized, 'k2.k2_1'));
        $this->assertSame(['-', '-', 'subStrKey1' => '-'], Helper::arrayGet($desensitized, 'k2.subkey'));
    }

    public function testExtractMatchedDataKeys(): void
    {
        $reflector = new ReflectionMethod(Desensitizer::class, 'extractMatchedDataKeys');
        $reflector->setAccessible(true);

        $desensitizer = new Desensitizer();
        $caller = fn () => $reflector->invoke($desensitizer, ...func_get_args());

        $this->assertSame(['testkey'], $caller('testkey', [
            'k', 'k1.0', 'k1.x', 'testkey', 'k2.y',
        ]));
        $this->assertSame([], $caller('testkey', [
            'k1', 'k2.0',
        ]));
        $this->assertSame(['testkey'], $caller('testkey', [
            'k1', 'k2.0', 'testkey.x', 'testkey.0',
        ]));
        $this->assertSame(['testkey.x', 'testkey.0'], $caller('testkey.*', [
            'k1', 'k2.0', 'testkey.x', 'testkey.0',
        ]));
        $this->assertSame(['testkey.0.foo', 'testkey.y.foo'], $caller('testkey.*.foo', [
            'k1', 'k2.0', 'testkey.x', 'testkey.0.foo',
            'testkey.0.bar', 'testkey.y.foo',
        ]));
        $this->assertSame(['testkey.0'], $caller('testkey.0', [
            'k1', 'k2.0', 'testkey.x', 'testkey.0.foo',
            'testkey.0.bar', 'testkey.y.foo',
        ]));
    }
}
