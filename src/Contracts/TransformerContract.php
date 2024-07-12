<?php

namespace Leoboy\Desensitization\Contracts;

interface TransformerContract
{
    /**
     * invoke data transformation
     *
     * @param  mixed  $input
     * @return mixed
     */
    public function transform($input);
}
