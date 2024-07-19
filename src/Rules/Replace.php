<?php

namespace Leoboy\Desensitization\Rules;

use Leoboy\Desensitization\Contracts\RuleContract;

class Replace extends AbstractRule implements RuleContract
{
    protected mixed $replacement;

    public function __construct(mixed $replacement = '-')
    {
        $this->replacement = $replacement;
    }

    public function use(mixed $replacement = '-'): static
    {
        $this->replacement = $replacement;

        return $this;
    }

    public function transform($input)
    {
        return $this->replacement;
    }
}
