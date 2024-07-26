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
use Leoboy\Desensitization\Exceptions\TransformException;

/**
 * the abstract rule class. typically, it is recommended that you should extend
 * this class, it contains some usefull methods for all rules.
 */
abstract class AbstractRule implements RuleContract
{
    /**
     * create a concret rule object.
     */
    public static function create(): static
    {
        /** @phpstan-ignore new.static */
        return new static(...func_get_args());
    }

    /**
     * assert input is transformable.
     *
     * @throws TransformException
     */
    protected function assertTransformable(bool|callable $assertable, string $message): void
    {
        if (is_callable($assertable)) {
            $assertable = $assertable();
        }
        if (! $assertable) {
            throw new TransformException($message);
        }
    }

    /**
     * assert input data type is transformable.
     *
     * @throws TransformException
     */
    protected function assertTransformableType(mixed $input, string|array $type): void
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
     *
     * @throws TransformException
     */
    protected function assertLikeString($input): void
    {
        $this->assertTransformableType($input, ['string', 'integer', 'double']);
    }

    /**
     * assert input is callable.
     *
     * @param  mixed  $input
     * @return void
     *
     * @throws TransformException
     */
    protected function assertCallable($input)
    {
        $this->assertTransformable(
            is_callable($input),
            'The input type must be callable',
        );
    }
}
