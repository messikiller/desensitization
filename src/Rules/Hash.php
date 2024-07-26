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

use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Hashing\BcryptHasher;
use Leoboy\Desensitization\Contracts\RuleContract;
use Leoboy\Desensitization\Exceptions\TransformException;
use Throwable;

/**
 * encrypt input with specified hasher driver.
 */
class Hash extends AbstractRule implements RuleContract
{
    /**
     * the hasher driver to make hash.
     */
    protected HasherContract $hasher;

    /**
     * configuration for the specified driver.
     */
    protected array $options = [];

    public function __construct(?HasherContract $hasher = null)
    {
        if (is_null($hasher)) {
            $hasher = new BcryptHasher();
        }
        $this->use($hasher);
    }

    /**
     * which hasher driver to use
     */
    public function use(HasherContract $hasher): self
    {
        $this->hasher = $hasher;

        return $this;
    }

    /**
     * set hasher options
     */
    public function options(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function transform($input)
    {
        $this->assertLikeString($input);
        try {
            return $this->hasher->make($input, $this->options);
        } catch (Throwable $th) {
            throw new TransformException($th->getMessage());
        }
    }
}
