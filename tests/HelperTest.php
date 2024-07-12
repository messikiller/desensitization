<?php

use Leoboy\Desensitization\Helper;
use PHPUnit\Framework\TestCase;

final class HelperTest extends TestCase
{
    public function testArrayGet(): void
    {
        $data = [
            'foo' => 'test_string',
            'bar' => [
                'bar_string_keyless',
                'bar_key_1' => 'test_bar_string',
                'bar_key_2' => [
                    987654321,
                    '123',
                    'generic sentence',
                    '多字节字符串',
                ],
                'bar_array_key' => ['test'],
            ],
        ];

        $this->assertSame('test_string', Helper::arrayGet($data, 'foo'));
        $this->assertSame('bar_string_keyless', Helper::arrayGet($data, 'bar.0'));
        $this->assertSame(['test'], Helper::arrayGet($data, 'bar.bar_array_key'));
    }

    public function testArrayDot(): void
    {
        $data = [
            'foo' => 'str',
            'bar' => [
                'bar_key_string' => 'bar_key_val',
                'bar_val_alone',
                [
                    'subkey' => 'testsub',
                    'subval',
                ],
                123,
                null,
                0,
                false,
            ],
        ];
        $this->assertSame([
            'foo' => 'str',
            'bar.bar_key_string' => 'bar_key_val',
            'bar.0' => 'bar_val_alone',
            'bar.1.subkey' => 'testsub',
            'bar.1.0' => 'subval',
            'bar.2' => 123,
            'bar.3' => null,
            'bar.4' => 0,
            'bar.5' => false,
        ], Helper::arrayDot($data));
    }

    public function testArraySet(): void
    {
        $data = [
            'foo' => 'str',
            'bar' => [
                'bar_key_string' => 'bar_key_val',
                'bar_val_alone',
                [
                    'subkey' => 'testsub',
                    'subval',
                ],
                123,
                null,
                0,
                false,
            ],
        ];

        Helper::arraySet($data, 'foo', 'updated');
        $this->assertSame('updated', Helper::arrayGet($data, 'foo'));

        Helper::arraySet($data, 'foo', ['testkey' => 'testval']);
        $this->assertSame(['testkey' => 'testval'], Helper::arrayGet($data, 'foo'));

        Helper::arraySet($data, 'bar.1.*', 'testupdated');
        $this->assertSame([
            'subkey' => 'testupdated',
            'testupdated',
        ], Helper::arrayGet($data, 'bar.1'));
    }
}
