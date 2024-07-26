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

use Exception;

/**
 * we get specifiec security policy for each data attribute.
 */
interface GuardContract
{
    /**
     * determine which security policy to use
     *
     * @throws Exception
     */
    public function getSecurityPolicy(): SecurityPolicyContract;
}
