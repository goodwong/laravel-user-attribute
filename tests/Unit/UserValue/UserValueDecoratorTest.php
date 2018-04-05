<?php

namespace Tests\Unit\UserValue;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Goodwong\UserAttribute\UserValue;
use Goodwong\UserAttribute\Entities\UserValue as Value;
use Goodwong\UserAttribute\Entities\UserAttribute as Attribute;
use Goodwong\UserAttribute\Entities\UserAttributeGroup as AttributeGroup;

class UserValueDecoratorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testUserValue()
    {
        $user = UserValue::user(1)->context('test')->reviser(1);
        // 保存
        $origin = '13510614266';
        $user->group('Test')->code('telephone')->label('Telephone')->type('input.tel')->value($origin);
        $this->assertTrue($user->code('telephone')->value() === $origin);

        // 类型
        $user->code('age')->value(29);
        $this->assertTrue(gettype($user->code('age')->value()) === 'integer');

        // 其他值
        $user->code('telephone')->value('13410584620');
        $this->assertTrue($user->code('telephone')->value() != $origin);

        // 历史记录
        $this->assertEquals(count($user->code('telephone')->history()), 2);
        $user->code('telephone')->value('13410584620');
        $this->assertEquals(count($user->code('telephone')->history()), 2);

        // force write
        $user->code('telephone')->value('13410584620', true);
        $this->assertEquals(count($user->code('telephone')->history()), 3);
        // dump($user->code('telephone')->history()->toArray());

        // object type
        $object = (object)['name' => 'william', 'sex' => 'male', 'age' => 29];
        $user->code('contacts')->value($object);
        $this->assertEquals($object->name, $user->code('contacts')->value()->name);

        // array type
        $object = ['name' => 'william', 'sex' => 'male', 'age' => 29];
        $user->code('contacts')->value($object);
        $this->assertEquals($object['name'], $user->code('contacts')->value()['name']);

        // attribute
        $attribute = Attribute::where('context', 'test')->where('code', 'contacts')->value('id');
        $this->assertEquals($object['name'], $user->attribute($attribute)->value()['name']);

        // inc
        $user->code('age')->increase();
        $this->assertEquals($user->code('age')->value(), 30);
        $user->code('age')->increase(10);
        $this->assertEquals($user->code('age')->value(), 40);

        $this->assertEquals($user->code('reward')->increaseAndGet(500), 500);
        $this->assertEquals($user->code('reward')->value(), 500);

        // empty
        $user->code('age')->empty();
        $this->assertEquals($user->code('age')->value(), null);

        // tags
        $user->code('口味')->add('清淡');
        $this->assertArraySubset($user->code('口味')->addAndGet('麻辣'), ['清淡', '麻辣']);

        // values
        $codes = ['name', 'telephone', 'age', 'contacts', '口味'];
        $values = $user->code($codes)->values();
        // var_dump($values);
        $this->assertArraySubset(array_keys($values), $codes);

        $attributes = Attribute::pluck('id')->all();
        $values = $user->attribute($attributes)->values();
        // var_dump($values);
        $this->assertArraySubset(array_keys($values), $attributes);

    }
}
