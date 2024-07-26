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
use Leoboy\Desensitization\Rules\None;

/**
 * guard with none rule
 */
class NoneGuard extends RuleFixedGuard implements GuardContract
{
    public function __construct()
    {
        parent::__construct(new None);
    }
}
