<?php

namespace Leoboy\Desensitization\Contracts;

interface GuardContract
{
    /**
     * determine which security policy to use
     *
     * @throws Exception
     */
    public function getSecurityPolicy(): SecurityPolicyContract;
}
