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

namespace Leoboy\Desensitization\Contracts;

use Leoboy\Desensitization\Exceptions\TransformException;

interface TransformerContract
{
    /**
     * invoke data transformation
     *
     * @param  mixed  $input
     * @return mixed
     *
     * @throws TransformException
     */
    public function transform($input);
}
