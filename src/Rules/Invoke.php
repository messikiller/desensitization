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

use Closure;
use Leoboy\Desensitization\Contracts\RuleContract;

/**
 * special rule for the callable method.
 */
class Invoke extends AbstractRule implements RuleContract
{
    /**
     * handler for the rule.
     */
    protected Closure $callback;

    public function __construct(callable $callback)
    {
        $this->callback = Closure::fromCallable($callback);
    }

    /**
     * {@inheritDoc}
     */
    public function transform($input)
    {
        return call_user_func_array($this->callback, [$input]);
    }
}
