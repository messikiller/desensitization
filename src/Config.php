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

    /**
     * set congiuration item.
     */
    public function set(string $path, mixed $value): static
    {
        Helper::arraySet($this->config, $path, $value);

        return $this;
    }

    /**
     * get congiuration item.
     */
    public function get(string $path, mixed $default = null): mixed
    {
        return Helper::arrayGet($this->config, $path, $default);
    }

    /**
     * get all configuration items as array.
     */
    public function toArray(): array
    {
        return $this->config;
    }

    /**
     * get wildcard character configuration.
     */
    public function getWildcardChar(): string
    {
        return $this->get('wildcard_char');
    }

    /**
     * get key dot character configuration.
     */
    public function getKeyDot(): string
    {
        return $this->get('key_dot');
    }

    /**
     * whether skip transformation exception.
     */
    public function shouldSkipTransformationException(): bool
    {
        return boolval($this->get('skip_transformation_exception'));
    }

    /**
     * {@inheritDoc}
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->config[$offset]);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->config[$offset]);
    }
}
