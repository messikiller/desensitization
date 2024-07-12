<?php

namespace Leoboy\Desensitization\Policies;

use Leoboy\Desensitization\Contracts\AttributeContract;
use Leoboy\Desensitization\Contracts\RuleContract;
use Leoboy\Desensitization\Contracts\SecurityPolicyContract;

/**
 * simple security policy who always return the same rule
 */
class RuleFixedSecurityPolicy implements SecurityPolicyContract
{
    /**
     * specified rule
     */
    protected RuleContract $rule;

    public function __construct(RuleContract $rule)
    {
        $this->rule = $rule;
    }

    /**
     * {@inheritDoc}
     */
    public function decide(AttributeContract $attribute): RuleContract
    {
        return $this->rule;
    }
}
