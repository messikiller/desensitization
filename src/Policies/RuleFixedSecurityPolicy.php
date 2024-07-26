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
