<?php

namespace Leoboy\Desensitization\Tests;

use Leoboy\Desensitization\Desensitizer;
use Leoboy\Desensitization\Exceptions\DesensitizationException;
use Leoboy\Desensitization\Helper;
use Leoboy\Desensitization\Rules\Mask;
use Leoboy\Desensitization\Rules\Replace;
use PHPUnit\Framework\Attributes\DataProvider;
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
                'xyz' => 'LionelAndresMessi',
            ],
        ];
        $desensitized = $desensitizer->desensitize($data, [
            'k1' => (fn ($str) => strrev($str)),
            'k2.k2_1' => new Mask(),
            'k2.subkey.*' => new Replace('-'),
            'k2.xyz' => 'mask|use:-|repeat:2|padding:3',
        ]);

        $this->assertSame('gfedcba', Helper::arrayGet($desensitized, 'k1'));
        $this->assertSame('1***8', Helper::arrayGet($desensitized, 'k2.k2_1'));
        $this->assertSame(['-', '-', 'subStrKey1' => '-'], Helper::arrayGet($desensitized, 'k2.subkey'));
        $this->assertSame('Lio--ssi', Helper::arrayGet($desensitized, 'k2.xyz'));
    }

    public function testInvoke(): void
    {
        $desensitizer = new Desensitizer();

        $this->assertSame(
            '654321',
            $desensitizer->invoke(123456, fn ($str) => strrev($str))
        );
        $this->assertSame(
            'xxxx',
            $desensitizer->invoke('xyzabc', new Replace('xxxx'))
        );
        $this->assertSame(
            'xyz',
            $desensitizer->via(new Replace('xyz'))->invoke('lionel messi')
        );
    }

    public function testInvokeUnguardedException(): void
    {
        $desensitizer = new Desensitizer();

        $this->expectException(DesensitizationException::class);
        $this->expectExceptionMessageMatches('/^Guard is required.*/');
        $desensitizer->invoke('xyzabc', 'unguarded_type');
    }

    public function testDesensitizePriority(): void
    {
        $desensitizer = new Desensitizer();
        $desensitizer->via((new Mask())->use('-')->repeat(3)->padding(1));

        $data = [
            'k1' => 'abcdefg',
            'k2' => [
                'k2_1' => '13034124128',
                'k2_2' => 'lionel messi',
            ],
        ];

        $desensitized = $desensitizer->desensitize($data, [
            'k1' => (fn ($str) => strrev($str)),
            'k2.k2_1' => new Replace('*'),
            'k2.k2_2',
        ]);

        $this->assertSame('gfedcba', Helper::arrayGet($desensitized, 'k1'));
        $this->assertSame('*', Helper::arrayGet($desensitized, 'k2.k2_1'));
        $this->assertSame('l---i', Helper::arrayGet($desensitized, 'k2.k2_2'));
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

    public function testConfig(): void
    {
        $desensitizer = new Desensitizer();

        $this->assertSame(
            [
                'wildcardChar' => '*',
                'keyDot' => '.',
                'skipTransformationException' => false,
            ],
            $desensitizer->config()
        );

        $desensitizer->config('k1', 'v1');
        $this->assertSame('v1', $desensitizer->config('k1'));

        $data = [
            'a' => [
                'b' => [
                    'maradona' => 'king',
                    'messi' => 'goat',
                    'neymar' => 'tiger',
                    'suarez' => 'killer',
                ],
            ],
        ];

        $desensitizer->config('keyDot', '__');
        $desensitizer->config('wildcardChar', '-');

        $desensitized = $desensitizer->desensitize($data, [
            'a__b__m-' => (new Mask())->use('*')->padding(1)->repeat(3),
        ]);

        $this->assertSame([
            'a' => [
                'b' => [
                    'maradona' => 'k***g',
                    'messi' => 'g***t',
                    'neymar' => 'tiger',
                    'suarez' => 'killer',
                ],
            ],
        ], $desensitized);
    }

    #[DataProvider('parseDataProvider')]
    public function testParse($definition, $expectedClass, $input, $expectedOutput): void
    {
        $desensitizer = new Desensitizer();
        $testMaskRule = $desensitizer->parse($definition);
        $this->assertInstanceOf($expectedClass, $testMaskRule);
        $this->assertSame(
            $expectedOutput,
            $desensitizer->invoke($input, $testMaskRule)
        );
    }

    public function testParseFailureException(): void
    {
        $this->expectException(DesensitizationException::class);
        $desensitizer = new Desensitizer();
        $desensitizer->parse('invalid');
    }

    public static function parseDataProvider(): array
    {
        return [
            ['mask|use:$|repeat:4|padding:1', Mask::class, 'lionelmessi', 'l$$$$i'],
            ['replace:x', Replace::class, 'ronaldo', 'x'],
            ['replace:x|use:*', Replace::class, 'ronaldo', '*'],
        ];
    }
}
