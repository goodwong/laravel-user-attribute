<?php

namespace Goodwong\LaravelUserAttribute\Handlers;

use Goodwong\LaravelUserAttribute\Entities\UserAttributeGroup;
use Goodwong\LaravelUserAttribute\Entities\UserAttribute;
use Goodwong\LaravelUserAttribute\Entities\UserValue;

class UserDataHandler
{
    // 实例化

    /**
     * context
     * 
     * @var  string  $context
     */
    public $context;

    /**
     * constructor
     *
     * @param  string  $context
     * @return void
     */
    public function __construct($context)
    {
        $this->context = $context;
    }

    // 写入

    /**
     * set
     * 创建记录（soft delete老记录）
     * 
     * @param  integer  $user_id
     * @param  integer  $attribute_id
     * @param  mixed  $value
     * @return void
     */
    public function set($user_id, $attribute_id, $value)
    {
        $exist = $this->get($user_id, $attribute_id);
        if ($exist && $exist->value == $value) {
            return;
        }
        if ($exist) {
            $exist->delete();
        }
        UserValue::create(compact('user_id', 'attribute_id', 'value'));
    }

    /**
     * set by code
     * 若找不到，自动创建attribute
     * 
     * @param  integer  $user_id
     * @param  string  $code
     * @param  mixed  $value
     * @param  array  $additional (optional)
     *   * @param  string  $label (optional)
     *   * @param  string  $type (optional)
     *   * @param  string  $group_label (optional)
     * @return void
     */
    public function setByCode($user_id, $code, $value, $additional = [])
    {
        $attribute = UserAttribute::where('context', $this->context)
            ->where('code', $code)
            ->first();
        if ($attribute) {
            return $this->set($user_id, $attribute->id, $value);
        }

        // create attribute group
        $group_label = data_get($additional, 'group_label', '默认');
        $group = UserAttributeGroup::firstOrCreate([
            'label' => $group_label, 
            'context' => $this->context,
        ]);

        // create attribute
        $attribute = UserAttribute::create([
            'context' => $this->context, 
            'group_id' => $group->id, 
            'label' => data_get($additional, 'label', $code),
            'code' => $code,
            'type' => data_get($additional, 'type', 'input.text'),
        ]);

        return $this->set($user_id, $attribute->id, $value);
    }

    /**
     * set by label
     * 若找不到，自动创建attribute
     * 
     * @param  integer  $user_id
     * @param  string  $label
     * @param  mixed  $value
     * @param  array  $additional (optional)
     *   * @param  string  $code (optional)
     *   * @param  string  $type (optional)
     *   * @param  string  $group_label (optional)
     * @return void
     */
    public function setByLabel($user_id, $label, $value, $additional = [])
    {
        $attribute = UserAttribute::where('context', $this->context)
            ->where('label', $label)
            ->first();
        if ($attribute) {
            return $this->set($user_id, $attribute->id, $value);
        }

        // create attribute group
        $group_label = data_get((array)$additional, 'group_label', '默认');
        $group = UserAttributeGroup::firstOrCreate([
            'label' => $group_label, 
            'context' => $this->context,
        ]);

        // create attribute
        $attribute = UserAttribute::create([
            'context' => $this->context, 
            'group_id' => $group->id, 
            'label' => $label,
            'code' => data_get($additional, 'code'),
            'type' => data_get($additional, 'type', 'input.text'),
        ]);

        return $this->set($user_id, $attribute->id, $value);
    }

    // 读出

    /**
     * get value
     * 
     * @param  integer  $user_id
     * @param  integer  $attribute_id
     * @param  string  $default (optional)
     * @return  UserValue
     */
    public function get($user_id, $attribute_id, $default = null) // ignore any context
    {
        return UserValue::where('user_id', $user_id)->where('attribute_id', $attribute_id)->first();
    }

    /**
     * get value by code
     * 
     * @param  integer  $user_id
     * @param  string  $code
     * @param  string  $default (optional)
     * @return  UserValue
     */
    public function getByCode($user_id, $code, $default = null)
    {
        //
    }

    // 历史记录

    /**
     * values history
     * 历史记录（返回 [UserValue, UserValue, ...]）
     * 
     * @param  integer  $user_id
     * @param  integer  $attribute_id
     * @return Collection[UserValue]
     */
    public function history($user_id, $attribute_id)
    {
        //
    }

    // 批量读取（$attribute_ids 空则自动查询context下所有属性）

    /**
     * values of user
     * 单个用户所有数据
     * 
     * @param  integer  $user_id
     * @param  array[integer]  $attribute_ids (optional)
     * @return Collection[UserValue]
     */
    public function values($user_id, $attribute_ids = [])
    {
        //
    }

    /**
     * values of many user
     * 需要自行控制user_ids规模
     * 
     * @param  array[integer]  $user_ids
     * @param  array[integer]  $attribute_ids (optional)
     * @return Collection[UserValue]
     */
    public function valuesOfMany($user_ids, $attribute_ids = [])
    {
        //
    }

    // 其它的

    /**
     * count by attribute
     * 统计某属性的人数
     * 
     * @param  integer  $attribute_id (optional)
     * @return integer
     */
    public function count($attribute_id = null)
    {
        //
    }

    /**
     * get user ids by attribute
     * 按照某属性获取排序的用户ID
     * 
     * @param  integer  $attribute_id
     * @param  string  $sort_direction (optional)
     * @erturn array[integer]
     */
    public function getUserIds($attribute_id, $sort_direction = 'ASC')
    {
        //
    }

    /**
     * get all user id
     *  * 所有用户ID，按照某属性排序的
     *  * 内存排序
     * 
     * @param  integer  $sort_attribute_id (optional)
     * @param  string  $sort_direction (optional)
     * @erturn array[integer]
     */
    public function allUserIds($sort_attribute_id = null, $sort_direction = 'ASC')
    {
        //
    }

    /**
     * search by keyword
     * 
     * @param  string  $keyword
     * @return Collection[UserAttribute]
     */
    public function search($keyword)
    {
        //
    }
}
