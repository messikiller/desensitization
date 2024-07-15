# Leoboy Desensitization - 一个功能强大的 PHP 数据脱敏工具

一个功能强大的 PHP 数据脱敏工具，内置丰富的脱敏计算规则：掩码、加密、截断、替换等。还支持基于安全策略的动态授权脱敏。

## 特性

- 内置丰富的脱敏计算规则：掩码、加密、截断、替换等.
- 支持通过自定义的 Guard, SecurityPolicy 和 Rule， 完成复杂的动态授权脱敏.
- 支持对多维数组不同层级的键值对进行匹配脱敏
- 支持对单个输入值脱敏
- 支持集成到 Laravel 框架.

## 使用环境

- PHP >= 8.0
- Composer

## 快速开始

### 安装

```bash
composer require "leoboy/desensitization"
```

### 使用

- 初始化：

```php
use Leoboy\Desensitization\Desensitizer;

//实例化一个普通脱敏器
$localDesensitizer = new Desensitizer();
//创建一个全局的单例对象
$globalDesensitizer = Desensitizer::global();
//局部对象固化为全局脱敏器对象
$localDesensitizer->globalize();
```

- 应用脱敏规则：

```php
use Leoboy\Desensitization\Rule\Mask;
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

$desensitizer->invoke('abc123', fn ($str) => strrev($str)); // 321cba
$desensitizer->invoke('123456', (new Mask())->padding(2)->use(*)->repeat(3)); // 12***56

// 多维数组
$desensitizer->desensitize($data, [
    'foo' => (new Mask())->padding(2)->use(*)->repeat(3),
    'bar.baz' => fn ($str) => strrev($str),
    'bar.jax.*' => new CustomRule(),
]);
```

### Laravel 集成

对于 Laravel 框架，已经支持通过 Laravel 的包自动发现加载，无需手动安装。程序已经自动在 Laravel 容器中绑定了脱敏器的对象（除非通过`global`方法访问，否则返回的都是一个局部的脱敏器对象），可以通过提供的 Facade 快速访问脱敏对象：

```php
use  Leoboy\Desensitization\Facades\Desensitization;

Desensitization::global()->via(fn ($str) => strrev($str))->desensitize('abc123'); // 321cba

Desensitization::via(new Mask())->transform($data, [
    'foo' => new CustomRule(),
    'bar' => fn ($str) => strrev($str),
    'jax'
]);
```

## 按照动态分级策略执行脱敏

实际应用场景下，往往需要对不同等级的用户进行不同的脱敏处理，比如：管理员用户可以查看全部数据，普通用户只能看到部分数据。为了解决这个问题，本库提供了“守卫”、“安全策略”和“规则”三个接口定义。

```php
$desensitizer = new Desensitizer();
$desensitizer->via(new User())->transform($data, [
    'foo' => 'email',
    'bar.*' => 'password',
    'baz.jax' => 'phone'
])
```

- `transform` 方法中定义的字段属性类型一般应该为字符串，如果定义的是 `callable|RuleContract` 类型，则会优先执行，并不经过 `via` 中指定的 guard
- `via` 方法用来为当前脱敏程序指定要经过的守卫，也可以传入一个全局使用的规则或者回调，其参数类型为：`GuardContract|RuleContract|callable`

### RuleContract

自定义的规则类需要实现接口：`\Leoboy\Desensitization\Contracts\RuleContract`，规则类定义了如何对输入的具体值进行转换输出

```php
class CustomRule implements RuleContract
{
    public function transform($value)
    {
        return md5($value);
    }
}
```

目前包内置的规则有：

- `Leoboy\Desensitization\Rules\None`：无规则，直接返回输入值
- `Leoboy\Desensitization\Rules\Mask`：掩码规则，可以指定掩码字符、重复次数和填充长度等：`(new Mask())->use('*')->repeat(3)->padding(2)`
- `Leoboy\Desensitization\Rules\Replace`：替换规则，可以指定要替换的字符：`(new Replace('replacement_chars'))`
- `Leoboy\Desensitization\Rules\Cut`：截断规则，可以指定截断的长度：`(new Cust())->start(1)->length(3)`
-  `Leoboy\Desensitization\Rules\Invoke`：可以执行指定的`callable`定义，`(new Invoke(fn ($str) => strrev($str)))`
- `Leoboy\Desensitization\Rules\Hash`：加密规则，可以指定 Hasher driver 和哈希参数，构造方法或者`use`方法传入的 Hasher driver 应当实现接口：`Illuminate\Contracts\Hashing\Hasher`，默认的加密算法为 Bcrypt 驱动，`(new Hash())->use(new Illuminate\Hashing\BcryptHasher())->options(['cost' => 10])`
- `Leoboy\Desensitization\Rules\Mix`，指定执行多条规则，要执行的规则列表通过构造方法传入：`(new Mix([new Replace('*'), new Mask()]))`

### GuardContract

程序通过守卫来获取安全策略，普通应用场景中，守卫一般可以是用户模型，也可以是一个自定义的类，但是两者都需要实现接口：`\Leoboy\Desensitization\Contracts\GuardContract`

例如：

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

包内置的守卫有：

- `Leoboy\Desensitization\Guards\PolicyFixedGuard`：这个守卫会返回固定的安全策略对象
- `Leoboy\Desensitization\Guards\RuleFixedGuard`：这个守卫会返回和安全策略绑定的固定的规则对象

### SecurityPolicyContract

安全策略类需要实现接口`Leoboy\Desensitization\Contracts\SecurityPolicyContract`，安全策略会根据用户输入的属性对象，决定如何对用户传入的属性字段进行脱敏，对于不同的属性，可以定义使用不同规则、闭包来进行脱敏。

```php
class CustomHighLevelSecurityPolicy implements SecurityPolicyContract
{
    public function decide(AttributeContract $attribute): RuleContract|callable
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
```

包内置的安全策略有：

- `Leoboy\Desensitization\SecurityPolicy\UnlimitedSecurityPolicy`：这个安全策略会固定返回规则对象：`Leoboy\Desensitization\Rule\None`，这一安全策略不会对数据进行任何处理，最终返回的字段值和输入的字段值始终保持一致
- `Leoboy\Desensitization\SecurityPolicy\RuleFixedSecurityPolicy`：这个安全策略会返回固定的规则对象
