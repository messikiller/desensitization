# desensitization

```php

class CustomHighLevelSecurityPolicy implements SecurityPolicyContract
{
    public function decide(AttributeContract $attribute): RuleContract
    {
        return match ($attribute->getType()) {
            'email' => new Replace('-'),
            'username' => new CustomRule(),
            'password' => function ($username) {
                return md5($username . mt_rand(100, 999));
            },
            default => (new Mask())->use('*')->repeat(3)->padding(2)
        };
    }
}

class User implements Guard
{
    public function getSecurityPolicy(): SecurityPolicyContract
    {
        return match (true) {
            $this->isAdministrator() => new CustomHighLevelSecurityPolicy(),
            default => new UnlimitedSecurityPolicy()
        }
    }
}

$desensitizer = new Desensitizer(new User());
$desensitizer = new Desensitizer(new CustomRule());
$desensitizer = new Desensitizer(fn ($value) => strrev($value));

$data = $desensitizer->desensitize($data, [
    'foo.bar' => 'email',
    'foo.*.baz' => 'phone',
    'foo.tom' => new CustomRule(),
    '*.jerry' => fn ($value) => strrev($value),
]);

Desensitizer::invoke($input, new User(), 'email');
Desensitizer::invoke($input, new Mask());
Desensitizer::invoke($input, new CustomRule());
Desensitizer::invoke($input, fn ($value) => strrev($value));
Desensitizer::invoke($input, [$customerProcesser, 'process']);
```
