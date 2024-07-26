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

namespace Leoboy\Desensitization\Contracts;

/**
 * the security policy tells us which rule to use for a given attribute
 */
interface SecurityPolicyContract
{
    /**
     * determine which rule to use for the given attribute
     */
    public function decide(AttributeContract $attribute): RuleContract|callable|string;
}
