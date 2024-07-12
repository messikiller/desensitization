<?php

namespace Leoboy\Desensitization\Rules;

use Leoboy\Desensitization\Contracts\RuleContract;

class Mask implements RuleContract
{
    protected string $aterisk = '*';

    protected int $repeatTimes = 3;

    protected int $paddingLeft = 1;

    protected int $paddingRight = 1;

    public function transform($input)
    {
        $len = mb_strlen($input);
        $left = $this->paddingLeft;
        $right = $this->paddingRight;

        if ($len <= $left + $right) {
            return str_repeat($this->aterisk, $this->repeatTimes);
        }

        $leftChars = mb_substr($input, 0, $left);
        $rightChars = mb_substr($input, $len - $right);

        return $leftChars.str_repeat($this->aterisk, $this->repeatTimes).$rightChars;
    }

    public function use(string $aterisk): self
    {
        $this->aterisk = $aterisk;

        return $this;
    }

    public function repeat(int $times): self
    {
        $this->repeatTimes = $times;

        return $this;
    }

    public function left(int $length): self
    {
        $this->paddingLeft = $length;

        return $this;
    }

    public function right(int $length): self
    {
        $this->paddingRight = $length;

        return $this;
    }

    public function padding(int $length): self
    {
        $this->left($length);
        $this->right($length);

        return $this;
    }
}
