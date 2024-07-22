<?php

namespace Leoboy\Desensitization;

use ArrayAccess;

/**
 * configuration for desensitization.
 *
 * @implements ArrayAccess<string, mixed>
 */
class Config implements ArrayAccess
{
    /**
     * default config.
     *
     * @var array<string, mixed>
     */
    protected array $config = [
        'wildcard_char' => '*',
        'key_dot' => '.',
        'skip_transformation_exception' => false,
    ];

    public function __construct(array $config)
    {
        $this->config = array_merge_recursive($this->config, $config);
    }

    public function set(string $path, mixed $value): static
    {
        Helper::arraySet($this->config, $path, $value);

        return $this;
    }

    public function get(string $path, mixed $default = null): mixed
    {
        return Helper::arrayGet($this->config, $path, $default);
    }

    public function toArray(): array
    {
        return $this->config;
    }

    public function getWildcardChar(): string
    {
        return $this->get('wildcard_char');
    }

    public function getKeyDot(): string
    {
        return $this->get('key_dot');
    }

    public function shouldSkipTransformationException(): bool
    {
        return boolval($this->get('skip_transformation_exception'));
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->config[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->config[$offset]);
    }
}
