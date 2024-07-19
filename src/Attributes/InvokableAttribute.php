<?php

namespace Leoboy\Desensitization\Attributes;

use Leoboy\Desensitization\Contracts\AttributeContract;
use Leoboy\Desensitization\Contracts\TransformerContract;
use Leoboy\Desensitization\Exceptions\DesensitizationException;

/**
 * attribute for invokable or callable function
 */
class InvokableAttribute implements AttributeContract, TransformerContract
{
    /**
     * @var string[]
     */
    protected array $dataKeys = [];

    /**
     * callback for transformation
     *
     * @var callable
     */
    protected $callback;

    /**
     * @param  string[]  $dataKeys
     */
    public function __construct(callable $callback, array $dataKeys = [])
    {
        $this->callback = $callback;
        $this->dataKeys = $dataKeys;
    }

    /**
     * {@inheritDoc}
     */
    public function getKey(): string
    {
        throw new DesensitizationException('Unsupported operation');
    }

    /**
     * {@inheritDoc}
     */
    public function getType(): string
    {
        throw new DesensitizationException('Unsupported operation');
    }

    /**
     * {@inheritDoc}
     */
    public function getDataKeys(): array
    {
        return $this->dataKeys;
    }

    /**
     * {@inheritDoc}
     */
    public function transform(mixed $input): mixed
    {
        return call_user_func_array($this->callback, [$input]);
    }
}
