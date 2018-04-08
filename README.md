# laravel-user-value


```php
use Goodwong\UserValue\UserValue;

///////////////
// 单用户操作
///////////////

// 创建对象
$user = UserValue::user(15); // 指定用户
$user->context('campaign_3') // 制定场景，默认"default“
$user->reviser(25); // 记录操作人，可选，默认是当前Auth用户

// 链式调用
$user = UserValue::user(15)->context('campaign_3')->reviser(25);

// 按照key写入
$user->group('联系方式') // 可选，默认“默认”，用于新创建的属性
     ->code('address')
     ->label('房号') // 可选，默认同 code，用于接下来新创建的属性
     ->type('input.text') // 可选，默认input.text，用于接下来新创建的属性
     ->value('1D2312')

// 按照id写入
UserValue::user(15) // 可以不需要context
     ->attribute(15) // 属性ID 15，常用于用户自定义属性(没有code)
     ->value('1栋D座309');

// 强制记录
$user->code('logs')->value('浏览「线上预约报名」', $forceWrite); // 默认只有内容与数据库不一样，才会写入新内容，
                                                             // 加上这个参数会强制写入，适合用于记录日志内容

// 支持文本、数字、数组、对象
$contacts = (object)['name' => 'william', 'age' => 29];
$user->code('contacts')
    ->value($contacts);
var_dump($user->code('contacts')->value())
// => {#1159
//      +"name": "william",
//      +"age": 29,
//    }

$user->code('telephone')
    ->value('13510614266');
var_dump($user->code('telephone')->value())
// => "13510614266"

$user->code('age')
    ->value(29);
var_dump($user->code('age')->value())
// => 29


// 其他方法
$user->code('form#15 viewed')
    ->increase();
$user->code('reward')
    ->increase(500);
echo $user->code('reward')
    ->increaseAndGet(500);

$user->code('口味')
    ->add('清淡'); // 只对数组或新属性操作，类型不对会抛出异常
var_dump($user->code('口味')->addAndGet('清淡'));
$user->code('telephone')
    ->empty();




// 读
$user->code('reward')->value()
UserValue::user(15)
    ->context('global')
    ->code(['name', 'telephone', 'address'])
    ->values()
// 返回：{ name: 'william', telephone: '13510614266', address: null } // 无数据则为null
UserValue::user(15)
    ->attribute([1, 3, 51, 92])
    ->values()
// 返回：{ 1: 'william', 3: null, 51: '13510614266', 92: null } // 无数据则为null

// ⚠️注意：
// 1. code／attribute／label／type 会在 value/values/inc/empty/history这些操作后失忆
// 2. context／reviser／group 则会一直记住

// 获取数据修改历史
$user->code('logs')
    ->history()
// 返回：[{ user_id, reviser_id, attribute_id, value, created_at }]




///////////////
// 用户内容
///////////////
$handler = UserValue::context('global');

// 搜索
$handler->code('name')
    ->search('测试') // 在 name 里面搜索“测试”
$handler->code(['name', 'telephone'])
    ->search('测试') // 在 name/telephone 里面搜索“测试”
$handler->attribute(25)
    ->search('测试') // 在 属性25... 里面搜索“测试”
$handler->attribute([25, 34])
    ->search('测试') // 在 属性25/34... 里面搜索“测试”
// 返回：[{ user_id, reviser_id, attribute_id, value, created_at }]

// 查询多人的数据（适用于CRM大表格）
$handler->code(['name', 'telephone'])
    ->valuesOfMany($userIds = [1, 2, 3, 4, 5]);
$handler->attribute([1, 2, 3, 4])
    ->valuesOfMany($userIds = [1, 2, 3, 4, 5]);
// 返回：[{ user_id, reviser_id, attribute_id, value, created_at }]




///////////////
// 用户列表操作
///////////////
$handler = UserValue::context('global');

UserValue::context('campaign_3')
    ->users()
    // 返回：[1, 2, 3, 4, ...., 100]


// 限定日期
UserValue::context('campaign_3')
    // 限定日期范围
    // 2018-3-23至今
    // 也可以：->dateRange('2018-3-23', '2018-4-1')
    ->dateRange('2018-3-23')
    ->users()

// 排序
UserValue::context('campaign_3')
    ->sortByTime('name', 'asc') // ->sortByTime(15, 'asc')
    // 或者 ->sortByValue('name', 'asc') // ->sortByValue(15, 'asc')
    ->users()

// 获取某个属性下都有值用户
UserValue::context('campaign_3')
    ->code('submit@form_13') // ->attribute(28)
    ->sortByTime('asc')
    ->users()

// 过滤
UserValue::context('campaign_3')
    ->filter('颜色', '绿色')
    ->filter('颜色', ['红色', '绿色'])
    ->filter(29, ['川味', '湘赣味']) // 多次调用，用与多列同时过滤
    ->users()

// 综合
UserValue::context('campaign_3')
    ->dateRange('2018-3-23')
    ->sortByValue('name', 'asc') // ->sortByValue(12, 'asc')
    ->users()


```