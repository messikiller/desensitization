<?php

namespace Leoboy\Desensitization\Guards;

use Leoboy\Desensitization\Contracts\GuardContract;
use Leoboy\Desensitization\Rules\None;

/**
 * guard with none rule
 */
class NoneGuard extends RuleFixedGuard implements GuardContract
{
    public function __construct()
    {
        parent::__construct(new None());
    }
}
