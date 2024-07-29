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
 * hide some characters of input with specified characters.
 */
class Mask extends AbstractRule implements RuleContract
{
    /**
     * mask asterisk.
     */
    protected string $aterisk = '*';

    /**
     * mask aterisk repeat times.
     */
    protected int $repeatTimes = 3;

    /**
     * left padding length.
     */
    protected int $paddingLeft = 1;

    /**
     * right padding length.
     */
    protected int $paddingRight = 1;

    /**
     * {@inheritDoc}
     */
    public function transform($input)
    {
        $this->assertLikeString($input);

        $len = mb_strlen($input);
        $left = $this->paddingLeft;
        $right = $this->paddingRight;

        if ($len < $left + $right) {
            return str_repeat($this->aterisk, $this->repeatTimes);
        }

        $leftChars = mb_substr($input, 0, $left);
        $rightChars = mb_substr($input, $len - $right);

        return $leftChars.str_repeat($this->aterisk, $this->repeatTimes).$rightChars;
    }

    /**
     * which character to use as mask.
     */
    public function use(string $aterisk): self
    {
        $this->aterisk = $aterisk;

        return $this;
    }

    /**
     * how long to repeat the aterisk.
     */
    public function repeat(int $times): self
    {
        $this->repeatTimes = $times;

        return $this;
    }

    /**
     * define left padding length.
     */
    public function left(int $length): self
    {
        $this->paddingLeft = $length;

        return $this;
    }

    /**
     * define right padding length.
     */
    public function right(int $length): self
    {
        $this->paddingRight = $length;

        return $this;
    }

    /**
     * define left and right padding length.
     */
    public function padding(int $length): self
    {
        $this->left($length);
        $this->right($length);

        return $this;
    }
}
