<?php

namespace Leoboy\Desensitization\Tests;

use Leoboy\Desensitization\Contracts\GuardContract;
use Leoboy\Desensitization\Contracts\RuleContract;
use Leoboy\Desensitization\Contracts\SecurityPolicyContract;
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
        $desensitizer = new Desensitizer;

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
            'k2.k2_1' => new Mask,
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
        $desensitizer = new Desensitizer;

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
        $this->assertSame(
            'Li###si',
            $desensitizer->invoke('LionelMessi', 'mask|use:#|repeat:3|padding:2')
        );
    }

    public function testInvokePriority(): void
    {
        $this->assertSame(
            'lio--ssi',
            (new Desensitizer)->invoke('lionel messi', 'mask|use:-|repeat:2|padding:3')
        );

        $this->assertSame(
            'lionel messi',
            (new Desensitizer)->invoke('lionel messi', 'invalid_rule_and_ungarded')
        );

        $this->assertSame(
            'lio--ssi',
            (new Desensitizer)->via('replace|user:xxx')->invoke('lionel messi', 'mask|use:-|repeat:2|padding:3')
        );

        $this->assertSame(
            '***',
            (new Desensitizer)->via(Replace::create('***'))->invoke('lionel messi', 'invalid_rule_but_guarded')
        );
    }

    public function testDesensitizePriority(): void
    {
        $desensitizer = new Desensitizer;
        $desensitizer->via((new Mask)->use('-')->repeat(3)->padding(1));

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

        $desensitizer = new Desensitizer;
        $caller = fn (...$args) => $reflector->invoke($desensitizer, ...$args);

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
        $desensitizer = new Desensitizer;

        $this->assertSame(
            [
                'wildcard_char' => '*',
                'key_dot' => '.',
                'skip_transformation_exception' => false,
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

        $desensitizer->config('key_dot', '__');
        $desensitizer->config('wildcard_char', '-');

        $desensitized = $desensitizer->desensitize($data, [
            'a__b__m-' => (new Mask)->use('*')->padding(1)->repeat(3),
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
    public function testParse(string $definition, string $expectedClass, mixed $input, mixed $expectedOutput): void
    {
        $desensitizer = new Desensitizer;
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
        $desensitizer = new Desensitizer;
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

    public function testGlobal(): void
    {
        $global = Desensitizer::global()->via('mask|use:$|repeat:3|padding:1');
        $this->assertSame($global, Desensitizer::global());
        $this->assertSame('l$$$i', Desensitizer::global()->invoke('lionelmessi'));
    }

    public function testGlobalize(): void
    {
        $local = new Desensitizer;
        $local->via('mask|use:*|repeat:2|padding:2')->globalize();
        $this->assertSame(Desensitizer::global(), $local);
        $this->assertSame('Cr**do', Desensitizer::global()->invoke('CristianoRonaldo'));
    }

    public function testVia(): void
    {
        $desensitizer = new Desensitizer;

        $testRule = $this->createStub(RuleContract::class);
        $testRule->method('transform')->willReturn('transformed');

        $testPolicy = $this->createStub(SecurityPolicyContract::class);
        $testPolicy->method('decide')->willReturn($testRule);

        $testGuard = $this->createStub(GuardContract::class);
        $testGuard->method('getSecurityPolicy')->willReturn($testPolicy);

        // via GuardContract
        $desensitizer->via($testGuard);
        $this->assertSame('transformed', $desensitizer->invoke('TestInput'));

        // via RuleContract
        $testRule2 = $this->createStub(RuleContract::class);
        $testRule2->method('transform')->willReturn('xyz');
        $desensitizer->via($testRule2);
        $this->assertSame('xyz', $desensitizer->invoke('TestInput'));

        // via SecurityPolicyContract
        $testPolicy2 = $this->createStub(SecurityPolicyContract::class);
        $testPolicy2->method('decide')->willReturn($testRule2);
        $desensitizer->via($testPolicy2);
        $this->assertSame('xyz', $desensitizer->invoke('TestInput'));

        // via callable
        $desensitizer->via(fn ($input) => strrev($input));
        $this->assertSame('tupnItseT', $desensitizer->invoke('TestInput'));

        // via string
        $desensitizer->via('replace|use:$$$');
        $this->assertSame('$$$', $desensitizer->invoke('TestInput'));
    }

    public function testRegister(): void
    {
        \Leoboy\Desensitization\Tests\Rules\Custom::$handler = fn ($input) => strrev($input);

        $desensitizer = new Desensitizer;
        $desensitizer->register(\Leoboy\Desensitization\Tests\Rules\Custom::class, 'custom');
        $this->assertSame('tupnItseT', $desensitizer->invoke('TestInput', 'custom'));
    }
}
