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
