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

/**
 * we use a 'attribute' to initialize the definition from user input.
 * the initilized atrribute will be used to execute the rule transfomation
 * or the specified security policy or guard.
 */
interface AttributeContract
{
    /**
     * attribute virtual unique key
     */
    public function getKey(): string;

    /**
     * get attribute type
     */
    public function getType(): string;

    /**
     * get data keys contained in virtual key
     *
     * @return string[]
     */
    public function getDataKeys(): array;
}
