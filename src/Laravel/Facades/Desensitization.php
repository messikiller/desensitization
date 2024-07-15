<?php

namespace Leoboy\Desensitization\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use Leoboy\Desensitization\Desensitizer;

/**
 * Facade for desensitization
 *
 * @method static via(GuardContract|RuleContract|SecurityPolicyContract|callable $guard)
 * @method static config(?string $key = null, $value = null)
 * @method array desensitize(array $data, array $definitions)
 * @method mixed invoke(mixed $value, string|RuleContract|callable $type)
 */
class Desensitization extends Facade
{
    public static function global(): Desensitizer
    {
        return Desensitizer::global(...func_get_args());
    }

    protected static function getFacadeAccessor()
    {
        return 'desensitizer';
    }
}
