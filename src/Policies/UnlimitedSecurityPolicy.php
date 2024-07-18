<?php

namespace Leoboy\Desensitization\Policies;

use Leoboy\Desensitization\Contracts\SecurityPolicyContract;
use Leoboy\Desensitization\Rules\None;

/**
 * security policy that allows unlimited access
 */
class UnlimitedSecurityPolicy extends RuleFixedSecurityPolicy implements SecurityPolicyContract
{
    public function __construct()
    {
        parent::__construct(new None());
    }
}
