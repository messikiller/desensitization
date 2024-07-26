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

use Leoboy\Desensitization\Contracts\AttributeContract;
use Leoboy\Desensitization\Contracts\GuardContract;
use Leoboy\Desensitization\Contracts\RuleContract;
use Leoboy\Desensitization\Contracts\SecurityPolicyContract;
use Leoboy\Desensitization\Contracts\TransformerContract;
use Leoboy\Desensitization\Exceptions\DesensitizationException;
use Leoboy\Desensitization\Exceptions\TransformException;
use Leoboy\Desensitization\Guards\NoneGuard;
use Throwable;

/**
 * the main desensitization utility class.
 */
class Desensitizer
{
    /**
     * global instance
     *
     * @var static|null
     */
    protected static $instance = null;

    /**
     * guarded security policy.
     */
    protected SecurityPolicyContract $policy;

    /**
     * configuration for desensitization.
     */
    protected Config $config;

    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        array $config = [],
        string|GuardContract|RuleContract|SecurityPolicyContract|callable|null $guard = null
    ) {
        if (is_null($guard)) {
            $guard = new NoneGuard();
        }
        $this->via($guard);
        $this->config = new Config($config);
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
        $this->policy = Factory::guard($definition)->getSecurityPolicy();

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
            return $this->config->toArray();
        }

        if (is_null($value)) {
            return $this->config->get($key, null);
        }

        $this->config->set($key, $value);

        return $this;
    }

    /**
     * register short rule
     */
    public function register(string $ruleClass, string $short, bool $override = false): static
    {
        if (! $override && RuleResolver::has($short)) {
            throw new DesensitizationException('The short name has already registered: '.$short);
        }

        RuleResolver::register($short, $ruleClass);

        return $this;
    }

    /**
     * parse the rule from short definition
     */
    public function parse(string $definition): RuleContract
    {
        return RuleResolver::resolve($definition);
    }

    /**
     * data desensitization method.
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
        $dotArray = Helper::arrayDot($data, $this->config->getKeyDot());
        $dotKeys = array_keys($dotArray);
        $attributes = [];

        foreach ($definitions as $key => $type) {
            if (is_string($key) && is_string($type)) {
                try {
                    $type = $this->parse($type);
                } catch (DesensitizationException $e) {
                    // skip
                }
            }
            if (is_int($key)) {
                [$key, $type] = [$type, '__TYPE__'];
            }
            $dataKeys = $this->extractMatchedDataKeys($key, $dotKeys);
            $attribute = Factory::attribute($key, $type, $dataKeys);
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
     *
     * @return mixed
     */
    public function invoke(mixed $value, string|RuleContract|callable $type = '')
    {
        try {
            $rule = Factory::rule($type);
        } catch (DesensitizationException $e) {
            $rule = Factory::rule($this->policy->decide(
                Factory::attribute('__KEY__', $type, ['__KEY__'])
            ));
        }

        try {
            return $rule->transform($value);
        } catch (Throwable $th) {
            if ($this->config->shouldSkipTransformationException()) {
                return $value;
            }
            throw new TransformException(sprintf(
                'Data transformation failed, Reason: %s, Value: %s',
                $th->getMessage(),
                var_export($value, true)
            ));
        }
    }

    /**
     * flatten array keys with dot
     */
    protected function extractMatchedDataKeys(string $key, array $dotKeys): array
    {
        $wildcardChar = $this->config->getWildcardChar();
        $keyDot = $this->config->getKeyDot();
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
        foreach ($attributes as $attribute) {
            $guardedRule = Factory::rule($this->policy->decide($attribute));
            foreach ($attribute->getDataKeys() as $key) {
                $original = Helper::arrayGet(
                    $data,
                    $key,
                    null,
                    $this->config->getKeyDot()
                );
                try {
                    $transformed = match (true) {
                        ($attribute instanceof TransformerContract) => $attribute->transform($original),
                        default => $guardedRule->transform($original),
                    };
                } catch (Throwable $th) {
                    if ($this->config->shouldSkipTransformationException()) {
                        continue;
                    }
                    throw new TransformException('Attribute transformation failed, value: '.var_export($attribute, true));
                }
                Helper::arraySet(
                    $data,
                    $key,
                    $transformed,
                    true,
                    $this->config->getKeyDot(),
                    $this->config->getWildcardChar()
                );
            }
        }

        return $data;
    }
}
