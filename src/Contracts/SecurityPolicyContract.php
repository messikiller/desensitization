<?php

namespace Leoboy\Desensitization\Contracts;

interface SecurityPolicyContract
{
    /**
     * determine which rule to use for the given attribute
     */
    public function decide(AttributeContract $attribute): RuleContract|callable;
}
