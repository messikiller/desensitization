<?php

declare(strict_types=1);

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

namespace Leoboy\Desensitization\Rules;

use Leoboy\Desensitization\Contracts\RuleContract;

/**
 * does nothing for the input.
 */
class None extends AbstractRule implements RuleContract
{
    /**
     * {@inheritDoc}
     */
    public function transform($input)
    {
        return $input;
    }
}
