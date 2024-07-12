<?php

namespace Leoboy\Desensitization\Rules;

use Leoboy\Desensitization\Contracts\RuleContract;
use Leoboy\Desensitization\Exceptions\InvalidRuleException;

class Mix implements RuleContract
{
    /**
     * bound rules list
     *
     * @var RuleContract[]
     */
    protected array $rules = [];

    public function __construct(array $rules = [])
    {
        foreach ($rules as $rule) {
            if (! ($rule instanceof RuleContract)) {
                throw new InvalidRuleException('Rule must be instance of RuleContract: '.get_class($rule));
            }
        }
        $this->rules = $rules;
    }

    public function transform($input)
    {
        foreach ($this->rules as $rule) {
            $input = $rule->transform($input);
        }

        return $input;
    }
}
