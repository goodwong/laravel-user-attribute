# Laravel User Value

灵活存储用户信息

## 特色
> 1. 灵活添加字段
> 2. 支持数据变更记录
> 3. 可以记录修改者
> 4. 支持排序、过滤、搜索（`未完成`）


## 安装
1. 添加 子模块
```shell
git submodule add https://github.com/goodwong/laravel-user-value.git .packages/goodwong/laravel-user-value
```

2. 添加composer配置
```json
    "goodwong/laravel-user-value": "dev-master",
```

3. 更新composer
```shell
composer update
```


## 数据操作

操作指定用户的数据。

### 创建对象
```php
use Goodwong\UserValue\UserValue;

// 指定用户
$user = UserValue::user(15);
// 场景
$user->context('campaign_3') // 制定场景，默认"default“
// 操作者
$user->reviser(25); // 记录操作人，可选，默认是当前Auth用户
```


### 链式调用
```php
$user = UserValue::user(15)->context('campaign_3')->reviser(25);
```

### 写入
```php
// 按照code
$user->group('联系方式') // 可选，默认“默认”，用于新创建的属性
     ->code('address')
     ->label('房号') // 可选，默认同 code，用于接下来新创建的属性
     ->type('input.text') // 可选，默认input.text，用于接下来新创建的属性
     ->value('1D2312')

// 按照id写入
UserValue::user(15) // 可以不需要context
     ->attribute(15) // 属性ID 15，常用于用户自定义属性(没有code)
     ->value('1栋D座309');
```


### 重复写入
> 默认只有内容与数据库不一样，才会写入新内容，  
> 加上这个参数会强制写入，适合用于记录日志内容
```php
$user->code('logs')->value('浏览「线上预约报名」', $forceWrite = true); 
```


### 支持多种数据类型
> 支持`文本`、`数字`、`数组`、`对象`
```php
// 对象
$contacts = (object)['name' => 'william', 'age' => 29];
$user->code('contacts')
    ->value($contacts);
var_dump($user->code('contacts')->value())
// => {#1159
//      +"name": "william",
//      +"age": 29,
//    }

// 文本
$user->code('telephone')
    ->value('13510614266');
var_dump($user->code('telephone')->value())
// => "13510614266"

// 数字
$user->code('age')
    ->value(29);
var_dump($user->code('age')->value())
// => 29
```


### 数字增长
```php
// 自增，默认+1
$user->code('form#15 viewed')
    ->increase();

// 增加500
$user->code('reward')
    ->increase(500);

// 也可以是负数
$user->code('remains')
    ->increase(-1);

// 增加并返回增加后的数据
echo $user->code('reward')
    ->increaseAndGet(500);
```


### 添加标签(自动去重、自动排序)
> 只对数组或新属性操作，类型不对会抛出异常
```php
$user->code('口味')->add('清淡');
$user->code('口味')->add(['清淡', '爽口']);
var_dump($user->code('口味')->addAndGet('清淡'));
var_dump($user->code('口味')->addAndGet(['清淡', '爽口']));
```


### 删除数据
```php
$user->code('telephone')->empty();
```


### 读取数据
```php
// 读当个字段
$user->code('reward')->value()

// 读取多个字段
UserValue::user(15)
    ->context('global')
    ->code(['name', 'telephone', 'address'])
    ->values()
// 返回（array）
// {
//     name: 'william', 
//     telephone: '13510614266', 
//     address: null // 不存在的数据则为null
// }

// 也可以是属性ID
UserValue::user(15)
    ->attribute([1, 3, 51, 92])
    ->values()
// 返回：
// { 
//     1: 'william', 
//     3: null, 
//     51: '13510614266', 
//     92: null // 无数据则为null
// } 
```


```php
// 获取数据修改历史
$user->code('logs')
    ->history()
// 返回：[{ user_id, reviser_id, attribute_id, value, created_at }]
```

⚠️注意：
> 1. 设置的 `context`／`reviser`／`group` 会一直记住
> 2. 但是 `code`／`attribute`／`label`／`type` 会在 查询、写入、删除等操作后失忆


## 多用户数据

如获取用户列表、筛选、过滤、排序、搜索等等。

### 实例化
> - 指定场景，若没有多场景需求，可以使用 `default`  
> - 不同场景的用户、属性、数据是隔离的
```php
$handler = UserValue::context('default');
```

### 搜索
```php
// 单字段
$handler->code('name')
    ->search('测试') // 在 name 里面搜索“测试”

// 多字段
$handler->code(['name', 'telephone'])
    ->search('测试') // 在 name/telephone 里面搜索“测试”

// 根据属性ID搜索
$handler->attribute(25)
    ->search('测试') // 在 属性25... 里面搜索“测试”

// 根据多个属性ID搜索
$handler->attribute([25, 34])
    ->search('测试') // 在 属性25/34... 里面搜索“测试”
// 返回：[{ user_id, reviser_id, attribute_id, value, created_at }]
```

### 查询多人的数据（适用于CRM大表格）
```php
$handler->code(['name', 'telephone'])
    ->valuesOfMany($userIds = [1, 2, 3, 4, 5]);
// 返回：[{ user_id, reviser_id, attribute_id, value, created_at }]

// 也支持属性列表
$handler->attribute([1, 2, 3, 4])
    ->valuesOfMany($userIds = [1, 2, 3, 4, 5]);
// 返回：同上
```

## 用户ID列表

```php
// 场景下所有用ID
UserValue::context('campaign_3')
    ->users()
    // 返回：[1, 2, 3, 4, ...., 100]
```

限定日期
```php
//  2018-3-23 至今
UserValue::context('campaign_3')
    ->dateRange('2018-3-23')
    ->users()

// 2018-3-23 至 2018-4-1
UserValue::context('campaign_3')
    ->dateRange('2018-3-23', '2018-4-1')
    ->users()
```

排序
```php
// 按记录时间
UserValue::context('campaign_3')
    ->sortByTime('join', 'asc') // ->sortByTime(15, 'asc')
    ->users()

// 按内容排序
UserValue::context('campaign_3')
    ->sortByValue('address', 'asc') // ->sortByValue(16, 'asc')
    ->users()
```

指定某个字段
```php
// 指定字段
UserValue::context('campaign_3')
    ->code('submit@form_13') // ->attribute(28)
    ->users()

// 指定字段并且排序
UserValue::context('campaign_3')
    ->code('submit@form_13') // ->attribute(28)
    ->sortByTime('asc') // ->sortByValue('asc')
    ->users()
```

过滤
```php
UserValue::context('campaign_3')
    ->filter('颜色', '绿色')
    ->users()

// 按照属性ID、取并集
UserValue::context('campaign_3')
    ->filter(29, ['川味', '湘赣味'])
    ->users()

// 多次filter取交集
UserValue::context('campaign_3')
    ->filter('颜色', '绿色')
    ->filter('颜色', ['红色', '绿色'])
    ->filter(29, ['川味', '湘赣味'])
    ->users()
```

综合使用
```php
UserValue::context('campaign_3')
    ->dateRange('2018-3-23')
    ->filter('颜色', '绿色')
    ->sortByValue('颜色', 'asc')
    ->users()
```
