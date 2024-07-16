<?php

namespace Leoboy\Desensitization\Rules;

use Leoboy\Desensitization\Contracts\RuleContract;

class Replace extends AbstractRule implements RuleContract
{
    protected $replacement;

    public function __construct($replacement = '-')
    {
        $this->replacement = $replacement;
    }

    public function use($replacement = '-'): self
    {
        $this->replacement = $replacement;

        return $this;
    }

    public function transform($input)
    {
        return $this->replacement;
    }
}
