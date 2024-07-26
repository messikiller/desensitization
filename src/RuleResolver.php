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

use Leoboy\Desensitization\Contracts\RuleContract;
use Leoboy\Desensitization\Exceptions\RuleResolveException;
use ReflectionClass;

/**
 * resove the shorthand rule definition to the real rule class.
 */
class RuleResolver
{
    /**
     * registered rules.
     *
     * @var array<string, string>
     */
    protected static $registered = [
        'cut' => \Leoboy\Desensitization\Rules\Cut::class,
        'hash' => \Leoboy\Desensitization\Rules\Hash::class,
        'mask' => \Leoboy\Desensitization\Rules\Mask::class,
        'none' => \Leoboy\Desensitization\Rules\None::class,
        'replace' => \Leoboy\Desensitization\Rules\Replace::class,
    ];

    /**
     * resolve the shorthand rule definition to the real rule class.
     *
     * @throws RuleResolveException
     */
    public static function resolve(string $definition): RuleContract
    {
        [$nameParams, $methodParams] = str_contains($definition, '|')
            ? explode('|', $definition, 2)
            : [$definition, ''];

        [$ruleName, $creationParams] = str_contains($nameParams, ':')
            ? explode(':', $nameParams, 2)
            : [$nameParams, ''];

        if (! static::has($ruleName)) {
            throw new RuleResolveException('The rule is not registered: '.$ruleName);
        }

        $ruleClass = static::$registered[$ruleName];
        $rule = new $ruleClass(...explode(',', $creationParams));

        foreach (explode('|', $methodParams) as $methodParam) {
            [$methodName, $params] = str_contains($methodParam, ':')
                ? explode(':', $methodParam)
                : [$methodParam, ''];
            if (method_exists($rule, $methodName)) {
                $rule->{$methodName}(...explode(',', $params));
            }
        }

        return $rule;
    }

    /**
     * check if the rule is registered.
     */
    public static function has(string $identifier): bool
    {
        return isset(static::$registered[$identifier]);
    }

    /**
     * register a customized rule definition.
     */
    public static function register(string $identifier, string $ruleClass): void
    {
        static::validate($ruleClass);
        static::$registered[$identifier] = $ruleClass;
    }

    /**
     * validate the definition available or not.
     *
     * @throws RuleResolveException
     */
    public static function validate(string $ruleClass): void
    {
        if (! class_exists($ruleClass)) {
            throw new RuleResolveException('The rule is not existed: '.$ruleClass);
        }
        $reflector = new ReflectionClass($ruleClass);
        if (! $reflector->implementsInterface(RuleContract::class)) {
            throw new RuleResolveException('The rule must implement RuleContract: '.$ruleClass);
        }
        if (! $reflector->isInstantiable()) {
            throw new RuleResolveException('The rule must be instantiable: '.$ruleClass);
        }
    }
}
