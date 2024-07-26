<?php

/*
 * This file is part of the Leoboy\Desensitization package.
 *
 * (c) messikiller <messikiller@aliyun.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Leoboy\Desensitization
 * @author messikiller <messikiller@aliyun.com>
 */

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
