<?php

namespace Leoboy\Desensitization\Rules;

use Leoboy\Desensitization\Contracts\RuleContract;
use Leoboy\Desensitization\Exceptions\TransformException;

abstract class AbstractRule implements RuleContract
{
    /**
     * create a concret rule object.
     */
    public static function create(): static
    {
        return new static(...func_get_args());
    }

    /**
     * assert input is transformable.
     *
     * @return void
     */
    protected function assertTransformable(bool|callable $assertable, string $message)
    {
        if (is_callable($assertable)) {
            $assertable = $assertable();
        }
        if (! $assertable) {
            throw new TransformException($message);
        }
    }

    protected function assertTransformableType($input, string|array $type)
    {
        $this->assertTransformable(
            in_array(gettype($input), (array) $type),
            sprintf('The input type must be %s.', implode(',', (array) $type)),
        );
    }

    /**
     * assert input is like string.
     *
     * @param  mixed  $input
     * @return void
     */
    protected function assertLikeString($input)
    {
        return $this->assertTransformableType($input, ['string', 'integer', 'double']);
    }

    /**
     * assert input is callable.
     *
     * @param  mixed  $input
     * @return void
     */
    protected function assertCallable($input)
    {
        $this->assertTransformable(
            is_callable($input),
            'The input type must be callable',
        );
    }
}
