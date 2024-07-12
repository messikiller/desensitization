<?php

namespace Leoboy\Desensitization\Contracts;

use Closure;

interface SecurityPolicyContract
{
    /**
     * determine which rule to use for the given attribute
     *
     * @return RuleContract|callable
     */
    public function decide(AttributeContract $attribute): RuleContract|callable;
}
