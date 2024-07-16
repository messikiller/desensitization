<?php

namespace Leoboy\Desensitization\Contracts;

use Exception;

interface GuardContract
{
    /**
     * determine which security policy to use
     *
     * @throws Exception
     */
    public function getSecurityPolicy(): SecurityPolicyContract;
}
