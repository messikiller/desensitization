<?php

namespace Leoboy\Desensitization;

use Leoboy\Desensitization\Attributes\GenericAttribute;
use Leoboy\Desensitization\Attributes\InvokableAttribute;
use Leoboy\Desensitization\Contracts\GuardContract;
use Leoboy\Desensitization\Contracts\RuleContract;
use Leoboy\Desensitization\Contracts\SecurityPolicyContract;
use Leoboy\Desensitization\Exceptions\DesensitizationException;
use Leoboy\Desensitization\Guards\PolicyFixedGuard;
use Leoboy\Desensitization\Guards\RuleFixedGuard;
use Leoboy\Desensitization\Rules\Invoke;

class Factory
{
    public static function attribute(
        string $key = '',
        string|RuleContract|callable $type = '',
        array $dataKeys = []
    ): GenericAttribute|InvokableAttribute {
        return match (true) {
            is_string($type) => new GenericAttribute($key, $type, $dataKeys),
            ($type instanceof RuleContract) => new InvokableAttribute([$type, 'transform'], $dataKeys),
            is_callable($type) => new InvokableAttribute($type, $dataKeys),
            default => throw new DesensitizationException('Attribute create failed')
        };
    }

    public static function guard(GuardContract|RuleContract|SecurityPolicyContract|callable $definition): GuardContract
    {
        return match (true) {
            ($definition instanceof GuardContract) => $definition,
            ($definition instanceof RuleContract) => new RuleFixedGuard(new Invoke([$definition, 'transform'])),
            ($definition instanceof SecurityPolicyContract) => new PolicyFixedGuard($definition),
            is_callable($definition) => new RuleFixedGuard(new Invoke($definition))
        };
    }
}
