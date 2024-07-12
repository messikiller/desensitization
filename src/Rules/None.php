<?php

namespace Leoboy\Desensitization\Rules;

use Leoboy\Desensitization\Contracts\RuleContract;

class None implements RuleContract
{
    public function transform($input)
    {
        return $input;
    }
}
