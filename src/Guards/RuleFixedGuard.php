<?php

namespace Leoboy\Desensitization\Guards;

use Leoboy\Desensitization\Contracts\GuardContract;
use Leoboy\Desensitization\Contracts\RuleContract;
use Leoboy\Desensitization\Contracts\SecurityPolicyContract;
use Leoboy\Desensitization\Policies\RuleFixedSecurityPolicy;

/**
 * simple guard which only use sticky rule.
 */
class RuleFixedGuard implements GuardContract
{
    protected RuleContract $rule;

    public function __construct(RuleContract $rule)
    {
        $this->rule = $rule;
    }

    public function getSecurityPolicy(): SecurityPolicyContract
    {
        return new RuleFixedSecurityPolicy($this->rule);
    }
}
