<?php

namespace Leoboy\Desensitization\Tests;

use Leoboy\Desensitization\Attributes\GenericAttribute;
use Leoboy\Desensitization\Attributes\InvokableAttribute;
use Leoboy\Desensitization\Factory;
use Leoboy\Desensitization\Rules\Mask;
use PHPUnit\Framework\TestCase;

final class FactoryTest extends TestCase
{
    public function testAttribute(): void
    {
        $attribute = Factory::attribute('testKey', 'testType', ['k1', 'k2']);
        $this->assertInstanceOf(GenericAttribute::class, $attribute);
        $this->assertSame('testKey', $attribute->getKey());
        $this->assertSame('testType', $attribute->getType());
        $this->assertSame(['k1', 'k2'], $attribute->getDataKeys());

        $attribute = Factory::attribute('testKey1', fn ($str) => strrev($str));
        $this->assertInstanceOf(InvokableAttribute::class, $attribute);
        $this->assertSame('dcba', $attribute->transform('abcd'));

        $attribute = Factory::attribute('testKey2', new Mask());
        $this->assertInstanceOf(InvokableAttribute::class, $attribute);
        $this->assertSame('a***z', $attribute->transform('abc123456567xyz'));
    }
}
