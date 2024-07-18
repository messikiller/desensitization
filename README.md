# Leoboy Desensitization - A Powerful PHP Data Desensitization Tool

A powerful PHP data desensitization tool with built-in rich desensitization calculation rules: masking, encryption, truncation, replacement, and more. It also supports dynamic authorization desensitization based on security policies.

<p align="center"><img src="logo.svg" width="90%" alt="Logo Leoboy Desensitization"></p>

## Features

- Rich built-in desensitization calculation rules: `mask`, `hash`, `cut`, `replace`, etc.
- Supports complex dynamic authorization desensitization through custom `Guard`, `SecurityPolicy`, and `Rule`.
- Supports matching and desensitizing key-value pairs at different levels of multi-dimensional arrays.
- Supports desensitizing single input values.
- Supports integration into the Laravel framework.

## Environment

- PHP >= 8.0
- Composer

## Quick Start

### Installation

```bash
composer require "leoboy/desensitization"
```

### Usage

- Initialization:

```php
use Leoboy\Desensitization\Desensitizer;

// Instantiate a regular desensitizer
$localDesensitizer = new Desensitizer();

// Create or get a global singleton object
$globalDesensitizer = Desensitizer::global();

// Make the local object a global desensitizer object
$localDesensitizer->globalize();
```

- Applying Desensitization Rules:

```php
use Leoboy\Desensitization\Desensitizer;
use Leoboy\Desensitization\Rules\Mask;
use App\Rules\CustomRule;

$data = [
    'foo' => 'tom',
    'bar' => [
        'baz' => 123,
        'jax' => [
            'jerry',
            'henry'
        ]
    ]
];

$desensitizer = new Desensitizer();

$desensitizer->invoke('abc123', fn ($str) => strrev($str)); // 321cba
$desensitizer->invoke('123456', Mask::create()->padding(2)->use(*)->repeat(3)); // 12***56
$desensitizer->invoke('123456', 'mask|use:x|repeat:3|padding:1'); // 1xxx6

$desensitizer = new Desensitizer();

// Multi-dimensional array
$desensitizer->desensitize($data, [
    'foo' => Mask::create()->padding(2)->use(*)->repeat(3),
    'bar' => new CustomRule(),
    'baz.*' => fn ($str) => strrev($str),
    'qux.*.fred' => 'mask|use:x|repeat:3|padding:4'
]);
```

### Laravel Integration

For the Laravel framework, it supports automatic package discovery and loading, eliminating the need for manual installation.

If you want to make changes in the configuration you can publish the config file `desensitization.php` using:

```bash
php artisan vendor:publish --provider="Leoboy\Desensitization\Laravel\DesensitizationServiceProvider"
```

content of the configuation file:

- `wildcardChar`, it define the wildcard character used in multi-dimension array searching, default is "*".
- `keyDot`, it define the key separator used in multi-dimension array searching, default is ".".
- `skipTransformationException`, tell the desensitizer to skip the exception thrown by the rule, default is boolean `false`.

The desensitizer object is automatically bound in the Laravel container (unless accessed through the `global` method, which returns a local desensitizer object). You can quickly access the desensitizer object through the provided Facade:

```php
use Leoboy\Desensitization\Laravel\Facades\Desensitization;

Desensitization::global()->via(fn ($str) => strrev($str))->invoke('abc123'); // 321cba

// return a local desensitizer object unless call global method
Desensitization::via(Mask::create())->desensitize($data, [
    'foo' => new CustomRule(),
    'bar' => fn ($str) => strrev($str),
    'jax' => 'mask|use:x|repeat:3|padding:4'
]);
```

## Executing Desensitization Based on Dynamic Hierarchical Policies

In practical application scenarios, it is often necessary to perform different desensitization processes for different levels of users, such as: administrators can view all data, while regular users can only see partial data. To address this issue, this library provides three interface definitions: "GuardContract", "SecurityPolicyContract", and "RuleContract".

```php
$desensitizer = new Desensitizer();
$desensitizer->via(new User())->desensitize($data, [
    'foo' => 'email',
    'bar.*' => 'mask|use:x|repeat:3|padding:4',
    'baz.jax' => Replace::create()->use('-'),
    'jaz' => fn ($input) => strrev($input),
    'frud.*'
])
```

- The field attribute types defined in the `transform` method should generally be strings. If a `string` type cannot be relsolved as `RuleContract` or `callable|RuleContract` type is defined, it will be executed first without going through the guard specified in `via`.
- The `via` method is used to specify the guard to be passed through for the current desensitization process, and can also pass in a globally used rule or callback. Its parameter types are: `string|GuardContract|RuleContract|SecurityPolicyContract|callable`.
- if the `via` method is not called, the default guard `\Leoboy\Desensitization\Guards\NoneGuard` will be used, the guard does nothing to transform input value.

### RuleContract

Custom rule classes need to implement the interface: `\Leoboy\Desensitization\Contracts\RuleContract`. The rule class defines how to transform the input value into an output.

```php
class CustomRule implements RuleContract
{
    public function transform($value)
    {
        return md5($value);
    }
}
```

Currently, the package includes the following built-in rules:

- `Leoboy\Desensitization\Rules\None`： No rule, returns the input value directly.
- `Leoboy\Desensitization\Rules\Mask`：Masking rule, allowing you to specify mask characters, repetition count, and padding length, etc.: `Mask::create()->use('*')->repeat(3)->padding(2)`
- `Leoboy\Desensitization\Rules\Replace`: Replacement rule, allowing you to specify characters to be replaced: `Replace::create()->use('replacement_chars')`
- `Leoboy\Desensitization\Rules\Cut`：Truncation rule, allowing you to specify the truncation length: `Cut::create()->start(1)->length(3)`
-  `Leoboy\Desensitization\Rules\Invoke`：Executes a specified `callable` definition: `Invoke::create(fn ($str) => strrev($str))`
- `Leoboy\Desensitization\Rules\Hash`：Encryption rule, allowing you to specify a Hasher driver and hash parameters. The Hasher driver passed into the constructor or `use` method should implement the interface:` Illuminate\Contracts\Hashing\Hasher`. The default encryption algorithm is the Bcrypt driver: `Hash::create()->use(new Illuminate\Hashing\BcryptHasher())->options(['cost' => 10])`
- `Leoboy\Desensitization\Rules\Mix`，Executes multiple rules, with the list of rules passed into the constructor: `Mix::create([Replace::create('*'), Mask::create()])`

Rules can also use by short name:

```
mask|use:x|repeat:3|padding:4
```

it has the same effect as:

```php
Mask::create()->use('x')->repeat(3)->padding(4)
```

currently, avaiable short names:

```php
[
    'cut' => \Leoboy\Desensitization\Rules\Cut::class,
    'hash' => \Leoboy\Desensitization\Rules\Hash::class,
    'mask' => \Leoboy\Desensitization\Rules\Mask::class,
    'none' => \Leoboy\Desensitization\Rules\None::class,
    'replace' => \Leoboy\Desensitization\Rules\Replace::class,
]
```

if you want to add a new customized rule and its short name, you can use:

```php
$desensitizer->register(\App\Rules\CustomRule::class, 'custom-rule');
```

if you want to overide a built-in rule, you can use (it may cause some unpredictable problems):

```php
$desensitizer->register(\App\Rules\CustomMaskRule::class, 'mask', true);
```

### GuardContract

The guard is used to obtain the security policy. In common scenarios, the guard can be a user model or a custom class, but both need to implement the interface: `\Leoboy\Desensitization\Contracts\GuardContract`

For example:

```php
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
```

Built-in guards include:

- `Leoboy\Desensitization\Guards\PolicyFixedGuard`：This guard returns a fixed security policy object.

- `Leoboy\Desensitization\Guards\RuleFixedGuard`: This guard returns a fixed rule object bound to the security policy.

### SecurityPolicyContract

The security policy class needs to implement the interface `Leoboy\Desensitization\Contracts\SecurityPolicyContract`. The security policy determines how to desensitize the user's input attribute fields based on the user's input attribute object. Different rules or closures can be defined for different attributes.

```php
class CustomHighLevelSecurityPolicy implements SecurityPolicyContract
{
    public function decide(AttributeContract $attribute): RuleContract|callable
    {
        return match ($attribute->getType()) {
            'email' => 'replace:*',
            'username' => new CustomRule(),
            'password' => function ($username) {
                return md5($username . mt_rand(100, 999));
            },
            default => Mask::create()->use('*')->repeat(3)->padding(2)
        };
    }
}
```

Built-in security policies include:

- `Leoboy\Desensitization\SecurityPolicy\UnlimitedSecurityPolicy`: This security policy always returns the: `Leoboy\Desensitization\Rule\None` rule object, which does not modify the data and returns the input field values as they are.
- `Leoboy\Desensitization\SecurityPolicy\RuleFixedSecurityPolicy`This security policy returns a fixed rule object.

## License

Desenseitization is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Contact with me

if you have any questions, you can issue a question.

With this powerful tool, you can flexibly define and apply desensitization rules based on your specific needs and security policies.

:heart: ENJOY IT!
