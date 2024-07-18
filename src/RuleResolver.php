<?php

namespace Leoboy\Desensitization;

use Leoboy\Desensitization\Contracts\RuleContract;
use Leoboy\Desensitization\Exceptions\RuleResolveException;
use ReflectionClass;

class RuleResolver
{
    /**
     * registered rules.
     *
     * @var RuleContract[string]
     */
    protected static $registered = [
        'cut' => \Leoboy\Desensitization\Rules\Cut::class,
        'hash' => \Leoboy\Desensitization\Rules\Hash::class,
        'mask' => \Leoboy\Desensitization\Rules\Mask::class,
        'none' => \Leoboy\Desensitization\Rules\None::class,
        'replace' => \Leoboy\Desensitization\Rules\Replace::class,
    ];

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

    public static function has(string $identifier): bool
    {
        return isset(static::$registered[$identifier]);
    }

    public static function register(string $identifier, string $ruleClass)
    {
        static::validate($ruleClass);
        static::$registered[$identifier] = $ruleClass;
    }

    public static function validate(string $ruleClass)
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
