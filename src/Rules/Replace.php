<?php

namespace Leoboy\Desensitization\Rules;

use Leoboy\Desensitization\Contracts\RuleContract;

class Replace implements RuleContract
{
    protected $replacement;

    public function __construct($replacement = '-')
    {
        $this->replacement = $replacement;
    }

    public function transform($input)
    {
        return $this->replacement;
    }
}
