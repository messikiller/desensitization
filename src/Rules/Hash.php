<?php

namespace Leoboy\Desensitization\Rules;

use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Hashing\BcryptHasher;
use Leoboy\Desensitization\Contracts\RuleContract;

class Hash implements RuleContract
{
    protected HasherContract $hasher;

    protected array $options = [];

    public function __construct(?HasherContract $hasher = null)
    {
        if (is_null($hasher)) {
            $hasher = new BcryptHasher();
        }
        $this->use($hasher);
    }

    public function use(HasherContract $hasher): self
    {
        $this->hasher = $hasher;

        return $this;
    }

    public function options(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function transform($input)
    {
        return $this->hasher->make($input, $this->options);
    }
}
