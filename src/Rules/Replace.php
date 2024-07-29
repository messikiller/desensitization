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
 * replace the input with a given value.
 */
class Replace extends AbstractRule implements RuleContract
{
    protected mixed $replacement;

    public function __construct(mixed $replacement = '-')
    {
        $this->replacement = $replacement;
    }

    /**
     * use the given value to replace the input.
     */
    public function use(mixed $replacement = '-'): static
    {
        $this->replacement = $replacement;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function transform($input)
    {
        return $this->replacement;
    }
}
