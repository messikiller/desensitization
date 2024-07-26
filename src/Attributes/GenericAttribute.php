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

namespace Leoboy\Desensitization\Attributes;

use Leoboy\Desensitization\Contracts\AttributeContract;

/**
 * generic attribute implementation
 */
class GenericAttribute implements AttributeContract
{
    /**
     * the virtual key of the attribute
     */
    protected string $key;

    /**
     * the type of the attribute. it can be one of the following:
     *
     * 1. a shorthand string value for the specified rule
     * 2. any string value for data type
     */
    protected string $type;

    /**
     * real data keys which contained in the attribute
     *
     * @var string[]
     */
    protected array $dataKeys = [];

    /**
     * @param  string[]  $dataKeys
     */
    public function __construct(string $key, string $type, array $dataKeys = [])
    {
        $this->key = $key;
        $this->type = $type;
        $this->dataKeys = $dataKeys;
    }

    /**
     * {@inheritDoc}
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * {@inheritDoc}
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * {@inheritDoc}
     */
    public function getDataKeys(): array
    {
        return $this->dataKeys;
    }
}
