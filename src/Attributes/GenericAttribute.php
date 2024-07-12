<?php

namespace Leoboy\Desensitization\Attributes;

use Leoboy\Desensitization\Contracts\AttributeContract;

/**
 * generic attribute implementation
 */
class GenericAttribute implements AttributeContract
{
    protected string $key;

    protected string $type;

    protected array $dataKeys = [];

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
