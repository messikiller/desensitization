<?php

/*
 * This file is part of the Leoboy\Desensitization package.
 *
 * (c) messikiller <messikiller@aliyun.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Leoboy\Desensitization
 * @author messikiller <messikiller@aliyun.com>
 */

namespace Leoboy\Desensitization;

use Leoboy\Desensitization\Attributes\GenericAttribute;
use Leoboy\Desensitization\Attributes\InvokableAttribute;
use Leoboy\Desensitization\Contracts\AttributeContract;
use Leoboy\Desensitization\Contracts\GuardContract;
use Leoboy\Desensitization\Contracts\RuleContract;
use Leoboy\Desensitization\Contracts\SecurityPolicyContract;
use Leoboy\Desensitization\Exceptions\DesensitizationException;
use Leoboy\Desensitization\Guards\PolicyFixedGuard;
use Leoboy\Desensitization\Guards\RuleFixedGuard;
use Leoboy\Desensitization\Rules\Invoke;

/**
 * the factory of desensitization.
 */
class Factory
{
    /**
     * produce a attribute object.
     *
     * @throws DesensitizationException
     */
    public static function attribute(string $key = '', mixed $type = '', array $dataKeys = []): AttributeContract
    {
        return match (true) {
            is_string($type) => new GenericAttribute($key, $type, $dataKeys),
            ($type instanceof RuleContract) => new InvokableAttribute([$type, 'transform'], $dataKeys),
            is_callable($type) => new InvokableAttribute($type, $dataKeys),
            default => throw new DesensitizationException('Attribute create failed')
        };
    }

    /**
     * produce a guard object.
     *
     * @throws DesensitizationException
     */
    public static function guard(mixed $definition): GuardContract
    {
        return match (true) {
            is_string($definition) => new RuleFixedGuard(RuleResolver::resolve($definition)),
            ($definition instanceof GuardContract) => $definition,
            ($definition instanceof RuleContract) => new RuleFixedGuard(new Invoke([$definition, 'transform'])),
            ($definition instanceof SecurityPolicyContract) => new PolicyFixedGuard($definition),
            is_callable($definition) => new RuleFixedGuard(new Invoke($definition)),
            default => throw new DesensitizationException('Guard create failed: '.var_export($definition, true))
        };
    }

    /**
     * produce a rule object.
     *
     * @throws DesensitizationException
     */
    public static function rule(mixed $definition): RuleContract
    {
        return match (true) {
            is_string($definition) => RuleResolver::resolve($definition),
            is_callable($definition) => new Invoke($definition),
            ($definition instanceof RuleContract) => $definition,
            default => throw new DesensitizationException('Rule create failed: '.var_export($definition, true))
        };
    }
}
