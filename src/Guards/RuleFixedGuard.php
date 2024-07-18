<?php

namespace Leoboy\Desensitization\Guards;

use Leoboy\Desensitization\Contracts\GuardContract;
use Leoboy\Desensitization\Contracts\RuleContract;
use Leoboy\Desensitization\Policies\RuleFixedSecurityPolicy;

/**
 * simple guard which only use sticky rule.
 */
class RuleFixedGuard extends PolicyFixedGuard implements GuardContract
{
    public function __construct(RuleContract $rule)
    {
        parent::__construct(new RuleFixedSecurityPolicy($rule));
    }
}
