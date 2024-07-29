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
use Leoboy\Desensitization\Exceptions\InvalidRuleException;

/**
 * use multiple rules to transform data
 */
class Mix extends AbstractRule implements RuleContract
{
    /**
     * bound rules list
     *
     * @var array<RuleContract>
     */
    protected array $rules = [];

    /**
     * @param  array<RuleContract>  $rules
     */
    public function __construct(array $rules = [])
    {
        foreach ($rules as $rule) {
            if (! ($rule instanceof RuleContract)) {
                throw new InvalidRuleException('Rule must be instance of RuleContract: '.$rule::class);
            }
        }
        $this->rules = $rules;
    }

    /**
     * append a rule to the rules list
     */
    public function append(RuleContract $rule): self
    {
        $this->rules[] = $rule;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function transform($input)
    {
        foreach ($this->rules as $rule) {
            $input = $rule->transform($input);
        }

        return $input;
    }
}
