<?php

namespace Leoboy\Desensitization;

use Leoboy\Desensitization\Contracts\AttributeContract;
use Leoboy\Desensitization\Contracts\GuardContract;
use Leoboy\Desensitization\Contracts\RuleContract;
use Leoboy\Desensitization\Contracts\SecurityPolicyContract;
use Leoboy\Desensitization\Contracts\TransformerContract;
use Leoboy\Desensitization\Exceptions\DesensitizationException;
use Leoboy\Desensitization\Exceptions\TransformException;
use Throwable;

class Desensitizer
{
    private static self $instance;

    /**
     * security guard
     */
    protected GuardContract $guard;

    /**
     * whether sef::$guard was set
     */
    protected bool $guarded = false;

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
        GuardContract|RuleContract|SecurityPolicyContract|callable|null $guard = null
    ) {
        if (! is_null($guard)) {
            $this->via($guard);
        }
        $this->config = array_merge($this->config, $config);
    }

    /**
     * get global instance
     */
    public static function global(): self
    {
        if (is_null(self::$instance)) {
            self::$instance = new self(...func_get_args());
        }

        return self::$instance;
    }

    /**
     * make current object as global
     */
    public function globalize(): self
    {
        self::$instance = $this;

        return self::$instance;
    }

    /**
     * set global security guard
     */
    public function via(GuardContract|RuleContract|SecurityPolicyContract|callable $guard): self
    {
        $this->guard = Factory::guard($guard);
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
                $key = $type;
                $type = '__TYPE__';
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
     * applu desensitization to standalone value
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
    public function invoke(mixed $value, string|RuleContract|callable $type): mixed
    {
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
            throw new TransformException('Data transformation failed, value: '.var_export($value, true));
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
                $original = Helper::arrayGet($data, $key);
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
                Helper::arraySet($data, $key, $transformed);
            }
        }

        return $data;
    }
}
