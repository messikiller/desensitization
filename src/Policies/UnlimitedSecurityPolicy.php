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
        parent::__construct(new None);
    }
}
