<?php

namespace Leoboy\Desensitization\Rules;

use Leoboy\Desensitization\Contracts\RuleContract;

class Cut implements RuleContract
{
    protected int $from = 0;

    protected ?int $length = null;

    public function __construct(int $from = 0, ?int $length = null)
    {
        $this->from = $from;
        $this->length = $length;
    }

    public function start(int $from = 0): self
    {
        $this->from = $from;

        return $this;
    }

    public function length(int $length): self
    {
        $this->length = $length;

        return $this;
    }

    public function transform($input)
    {
        return mb_substr($input, $this->from, $this->length);
    }
}
