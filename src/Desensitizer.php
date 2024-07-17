<?php

namespace Leoboy\Desensitization;

use Leoboy\Desensitization\Contracts\AttributeContract;
use Leoboy\Desensitization\Contracts\GuardContract;
use Leoboy\Desensitization\Contracts\RuleContract;
use Leoboy\Desensitization\Contracts\SecurityPolicyContract;
use Leoboy\Desensitization\Contracts\TransformerContract;
use Leoboy\Desensitization\Exceptions\DesensitizationException;
use Leoboy\Desensitization\Exceptions\TransformException;
use ReflectionClass;
use Throwable;

class Desensitizer
{
    /**
     * global instance
     *
     * @var static
     */
    protected static $instance;

    /**
     * security guard
     */
    protected GuardContract $guard;

    /**
     * whether sef::$guard was set
     */
    protected bool $guarded = false;

    /**
     * registered rules list: short => rule
     */
    protected array $shortRules = [
        'cut' => \Leoboy\Desensitization\Rules\Cut::class,
        'hash' => \Leoboy\Desensitization\Rules\Hash::class,
        'mask' => \Leoboy\Desensitization\Rules\Mask::class,
        'none' => \Leoboy\Desensitization\Rules\None::class,
        'replace' => \Leoboy\Desensitization\Rules\Replace::class,
    ];

    /**
     * default configuration for desensitization
     */
    protected array $config = [
        'wildcardChar' => '*',
        'keyDot' => '.',
        'skipTransformationException' => false,
    ];

    public function __construct(
        array $config = [],
        string|GuardContract|RuleContract|SecurityPolicyContract|callable|null $guard = null
    ) {
        if (! is_null($guard)) {
            $this->via($guard);
        }
        $this->config = array_merge($this->config, $config);
    }

    /**
     * get global instance
     */
    public static function global(): static
    {
        if (is_null(static::$instance)) {
            /** @phpstan-ignore new.static */
            static::$instance = new static(...func_get_args());
        }

        return static::$instance;
    }

    /**
     * make current object as global
     */
    public function globalize(): static
    {
        self::$instance = $this;

        return self::$instance;
    }

    /**
     * set global security guard
     */
    public function via(string|GuardContract|RuleContract|SecurityPolicyContract|callable $definition): self
    {
        if (is_string($definition)) {
            $definition = $this->parse($definition);
        }
        $this->guard = Factory::guard($definition);
        $this->guarded = true;

        return $this;
    }

    /**
     * set or get configuration
     *
     * @param  mixed  $value
     */
    public function config(?string $key = null, $value = null): mixed
    {
        if (is_null($key)) {
            return $this->config;
        }
        if (is_null($value)) {
            return Helper::arrayGet($this->config, $key, null);
        }
        Helper::arraySet($this->config, $key, $value);

        return $this;
    }

    /**
     * register short rule
     */
    public function register(string $ruleClass, string $short, bool $override = false): static
    {
        if (! class_exists($ruleClass)) {
            throw new DesensitizationException('The registering rule is not existed: '.$ruleClass);
        }
        $reflector = new ReflectionClass($ruleClass);
        if (! $reflector->implementsInterface(RuleContract::class)) {
            throw new DesensitizationException('The registering rule must implement RuleContract: '.$ruleClass);
        }
        if (! $reflector->isInstantiable()) {
            throw new DesensitizationException('The registering rule must be instantiable: '.$ruleClass);
        }
        if (! $override && isset($this->shortRules[$short])) {
            throw new DesensitizationException('The short name has already registered: '.$short);
        }
        $this->shortRules[$short] = $ruleClass;

        return $this;
    }

    /**
     * parse the rule from short definition
     */
    public function parse(string $definition): RuleContract
    {
        [$nameParams, $methodParams] = str_contains($definition, '|')
            ? explode('|', $definition, 2)
            : [$definition, ''];
        [$ruleName, $creationParams] = str_contains($nameParams, ':')
            ? explode(':', $nameParams, 2)
            : [$nameParams, ''];
        if (! isset($this->shortRules[$ruleName])) {
            throw new DesensitizationException('The rule is not registered: '.$ruleName);
        }
        $ruleClass = $this->shortRules[$ruleName];
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
     * data desensitization method
     *
     * definitions EXAMPLE:
     *  [
     *      'foo.bar' => 'email',
     *      'foo.*.baz' => new CustomRule(),
     *      'jax.*' => fn ($val) => strrev($val),
     * ]
     */
    public function desensitize(array $data, array $definitions): array
    {
        $dotArray = Helper::arrayDot($data, $this->config['keyDot']);
        $dotKeys = array_keys($dotArray);
        $attributes = [];

        foreach ($definitions as $key => $type) {
            if (is_int($key)) {
                [$key, $type] = [$type, '__TYPE__'];
            }
            if (is_string($type)) {
                try {
                    $type = $this->parse($type);
                } catch (DesensitizationException $e) {
                    // skip
                }
            }
            $dataKeys = $this->extractMatchedDataKeys($key, $dotKeys);
            $attribute = Factory::attribute($key, $type, $dataKeys);
            if (! $this->guarded && ! ($attribute instanceof TransformerContract)) {
                throw new DesensitizationException('Guard is required unless attribute is callable or rule, key: '.$key);
            }
            $attributes[] = $attribute;
        }

        return $this->transform($data, $attributes);
    }

    /**
     * apply desensitization to standalone value
     *
     * Usage:
     *
     * 1. transform vallue with rule directly:
     *      (new Desensitizer())->invoke($input, new CustomRule());
     * 2. transform value with callable directly:
     *      (new Desensitizer())->invoke($input, fn ($val) => strrev($val));
     * 3. transform value with global guard or rule or callback:
     *      (new Desensitizer())->via($guardOrRule)->invoke($input, email');
     */
    public function invoke(mixed $value, string|RuleContract|callable $type = ''): mixed
    {
        if (is_string($type) && ! empty($type)) {
            try {
                $type = $this->parse($type);
            } catch (DesensitizationException $e) {
                // skip
            }
        }
        if (! $this->guarded && is_string($type)) {
            throw new DesensitizationException('Guard is required unless attribute is callable or rule');
        }
        try {
            return match (true) {
                is_string($type) => $this->guard->getSecurityPolicy()->decide(
                    Factory::attribute('__KEY__', $type, ['__KEY__'])
                )->transform($value),
                ($type instanceof RuleContract) => $type->transform($value),
                is_callable($type) => call_user_func_array($type, [$value]),
            };
        } catch (Throwable $th) {
            if ($this->config['skipTransformationException']) {
                return $value;
            }
            throw new TransformException($th->getMessage().'Data transformation failed, value: '.var_export($value, true));
        }
    }

    /**
     * flatten array keys with dot
     */
    protected function extractMatchedDataKeys(string $key, array $dotKeys): array
    {
        $wildcardChar = $this->config['wildcardChar'];
        $keyDot = $this->config['keyDot'];
        if (! str_contains($key, $wildcardChar)) {
            $realKeysExisted = count(array_filter(
                $dotKeys,
                fn ($dotKey) => $dotKey == $key || strpos($dotKey, $key.$keyDot) === 0
            )) > 0;

            return $realKeysExisted ? [$key] : [];
        }

        $pattern = str_replace(
            preg_quote($wildcardChar),
            '([^('.preg_quote($keyDot).')]+)',
            preg_quote($key)
        );

        return array_values(array_filter(
            $dotKeys,
            fn ($dotKey) => (bool) preg_match('/^'.$pattern.'\z/', $dotKey)
        ));
    }

    /**
     * transform data with specific attributes
     *
     * @param  AttributeContract[]  $attributes
     */
    protected function transform(array $data, array $attributes): array
    {
        $securityPolicy = $this->guarded ? $this->guard->getSecurityPolicy() : null;
        foreach ($attributes as $attribute) {
            $guardedRule = $this->guarded ? $securityPolicy->decide($attribute) : null;
            foreach ($attribute->getDataKeys() as $key) {
                $original = Helper::arrayGet($data, $key, null, $this->config['keyDot']);
                try {
                    $transformed = match (true) {
                        ($attribute instanceof TransformerContract) => $attribute->transform($original),
                        is_callable($guardedRule) => call_user_func_array($guardedRule, [$original]),
                        ($guardedRule instanceof TransformerContract) => $guardedRule->transform($original),
                        default => throw new DesensitizationException('Transform attribute failed, attribute: '.var_export($attribute, true)),
                    };
                } catch (Throwable $th) {
                    if ($this->config['skipTransformationException']) {
                        continue;
                    }
                    throw new TransformException('Attribute transformation failed, value: '.var_export($attribute, true));
                }
                Helper::arraySet(
                    $data,
                    $key,
                    $transformed,
                    true,
                    $this->config['keyDot'],
                    $this->config['wildcardChar']
                );
            }
        }

        return $data;
    }
}
