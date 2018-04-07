<?php

namespace Tests\Unit\UserValue;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Goodwong\UserValue\UserValue;
use Goodwong\UserValue\Entities\UserValue as Value;
use Goodwong\UserValue\Entities\UserAttribute as Attribute;
use Goodwong\UserValue\Entities\UserAttributeGroup as AttributeGroup;

class UserValueHandlerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testUserValueHandler()
    {
        // user(1)
        $user = UserValue::user(1)->context('test')->reviser(1);
        $user->group('Test');
        $user->code('telephone')->label('Telephone')->type('input.tel')->value('13510614266');
        $user->code('age')->value(29);
        $user->code('contacts')->value((object)['name' => 'william', 'sex' => 'male', 'age' => 29]);
        $user->code('口味')->add('清淡');
        $user->code('口味')->add('麻辣');

        // user(2)
        UserValue::user(2)->context('test')->code('telephone')->value('18822892208');
        // user(3)
        UserValue::user(3)->context('test')->code('口味')->add('重口味');
        UserValue::user(3)->context('test')->code('口味')->add('清淡');

        // values
        $codes = ['name', 'telephone', 'age', 'contacts', '口味'];
        $handler = UserValue::context('test');
        $values = $handler->code($codes)->valuesOfMany([1]);
        $this->assertEquals(4, count($values));
        $this->assertEquals(3, count($handler->users()));
        $this->assertEquals(2, count($handler->code('口味')->users()));

        $this->assertEquals(2, count($handler->code('口味')->sortByValue('asc')->users()));
        $this->assertEquals(2, count($handler->code('口味')->sortByTime('desc')->users()));
        dump($handler->code('口味')->sortByTime('asc')->users());
        dump($handler->code('口味')->sortByTime('desc')->users());
        dump($handler->code('口味')->sortByValue('asc')->users());
        dump($handler->code('口味')->sortByValue('desc')->users());
        dump($handler->code('口味')->valuesOfMany([1, 2, 3])->toArray());
        $this->assertEquals(0, count($handler->dateRange(date('Y-m-d', time() + 86400 * 2))->sortByTime('desc')->users()));
        $this->assertEquals(3, count($handler->dateRange(date('Y-m-d', time() - 86400 * 1), date('Y-m-d', time() + 86400 * 2))->sortByTime('desc')->users()));
        $this->assertEquals(3, count($handler->sortByValue('口味', 'asc')->users()));
        dump($handler->sortByValue('口味', 'asc')->users());
        dump($handler->sortByValue('口味', 'desc')->users());
        dump($handler->sortByTime('口味', 'asc')->users());
        dump($handler->sortByTime('口味', 'desc')->users());
        $this->assertEquals(1, count($handler->filter('口味', '重口味')->users()));
        $this->assertEquals(2, count($handler->filter('口味', ['重口味', '麻辣'])->users()));
        $this->assertEquals(0, count($handler->filter('口味', ['重口味'])->filter('口味', '麻辣')->users()));
    }
}
