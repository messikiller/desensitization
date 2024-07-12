<?php

namespace Leoboy\Desensitization\Guards;

use Leoboy\Desensitization\Contracts\GuardContract;
use Leoboy\Desensitization\Contracts\SecurityPolicyContract;

/**
 * simple guard which only use sticky SecurityPolicyContract.
 */
class PolicyFixedGuard implements GuardContract
{
    protected SecurityPolicyContract $policy;

    public function __construct(SecurityPolicyContract $policy)
    {
        $this->policy = $policy;
    }

    public function getSecurityPolicy(): SecurityPolicyContract
    {
        return $this->policy;
    }
}
