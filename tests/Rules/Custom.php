<?php

namespace Leoboy\Desensitization\Tests\Rules;

use Closure;
use Leoboy\Desensitization\Contracts\RuleContract;
use Leoboy\Desensitization\Rules\AbstractRule;

class Custom extends AbstractRule implements RuleContract
{
    public static ?Closure $handler = null;

    public function transform($input)
    {
        if (is_null(self::$handler)) {
            return $input;
        }

        return (self::$handler)($input);
    }
}
