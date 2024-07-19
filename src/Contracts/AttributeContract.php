<?php

namespace Leoboy\Desensitization\Contracts;

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
