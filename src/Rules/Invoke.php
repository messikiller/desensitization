<?php

namespace Leoboy\Desensitization\Rules;

use Closure;
use Leoboy\Desensitization\Contracts\RuleContract;

class Invoke implements RuleContract
{
    protected Closure $callback;

    public function __construct(callable $callback)
    {
        $this->callback = Closure::fromCallable($callback);
    }

    public function transform($input)
    {
        return call_user_func_array($this->callback, [$input]);
    }
}
