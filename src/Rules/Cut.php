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

namespace Leoboy\Desensitization\Rules;

use Leoboy\Desensitization\Contracts\RuleContract;

/**
 * cut input string for desentization.
 */
class Cut extends AbstractRule implements RuleContract
{
    /**
     * where to start cut.
     */
    protected int $from = 0;

    /**
     * how long to cut.
     */
    protected ?int $length = null;

    public function __construct(int $from = 0, ?int $length = null)
    {
        $this->from = $from;
        $this->length = $length;
    }

    /**
     * define where to start cut.
     */
    public function start(int $from = 0): self
    {
        $this->from = $from;

        return $this;
    }

    /**
     * define how long to cut.
     */
    public function length(int $length): self
    {
        $this->length = $length;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function transform($input)
    {
        $this->assertLikeString($input);

        return mb_substr($input, $this->from, $this->length);
    }
}
