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
    public function __construct($context = null)
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
     * @return UserValue
     */
    public function set($user_id, $attribute_id, $value)
    {
        $exist = $this->get($user_id, $attribute_id);
        if ($exist && $exist->value == $value) {
            return $exist;
        }
        if ($exist) {
            $exist->delete();
        }
        return UserValue::create(compact('user_id', 'attribute_id', 'value'));
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
     * @return UserValue
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
     * @return UserValue
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
        $attribute = UserAttribute::where('context', $this->context)
            ->where('code', $code)
            ->first();
        if ($attribute) {
            return $this->get($user_id, $attribute->id, $default);
        }
        return null;
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
        return UserValue::withTrashed()
            ->where('user_id', $user_id)
            ->where('attribute_id', $attribute_id)
            ->get();
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
        if (!$attribute_ids) {
            $attribute_ids = UserAttribute::where('context', $this->context)->pluck('id')->all();
        }
        if (!$attribute_ids) {
            return collect();
        }

        return UserValue::where('user_id', $user_id)
            ->whereIn('attribute_id', $attribute_ids)
            ->get();
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
        if (!$attribute_ids) {
            $attribute_ids = UserAttribute::where('context', $this->context)->pluck('id')->all();
        }
        if (!$attribute_ids || !$user_ids) {
            return collect();
        }

        return UserValue::whereIn('user_id', $user_ids)
            ->whereIn('attribute_id', $attribute_ids)
            ->get();
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
        $query = UserValue::getModel();
        if ($attribute_id) {
            $query = $query->where('attribute_id', $attribute_id);
            return $query->distinct('user_id')->count('user_id');
        }

        // count user in current context
        $attribute_ids = UserAttribute::where('context', $this->context)->pluck('id')->all();
        if (!$attribute_ids) {
            return 0;
        }
        $query = $query->whereIn('attribute_id', $attribute_ids);
        return $query->distinct('user_id')->count('user_id');
    }

    /**
     * get user ids by attribute
     * 按照某属性获取排序的用户ID
     * 
     * @param  integer  $attribute_id
     * @param  string  $sort_order (optional)
     * @erturn array[integer]
     */
    public function getUserIds($attribute_id, $sort_order = 'ASC')
    {
        $values = UserValue::where('attribute_id', $attribute_id)
            ->pluck('value', 'user_id')->all();
        if (strtoupper($sort_order) === 'ASC') {
            asort($values);
        } else {
            arsort($values);
        }
        return array_keys($values);
    }

    /**
     * get all user id
     *  * 所有用户ID，按照某属性排序的
     *  * 内存排序
     * 
     * @param  integer  $sort_attribute_id (optional)
     * @param  string  $sort_order (optional)
     * @erturn array[integer]
     */
    public function allUserIds($sort_attribute_id = null, $sort_order = 'ASC')
    {
        $attribute_ids = UserAttribute::where('context', $this->context)->pluck('id')->all();
        if (!$attribute_ids) {
            return [];
        }

        // all ids
        $all_ids = UserValue::whereIn('attribute_id', $attribute_ids)
            ->distinct('user_id')
            ->pluck('user_id')->reverse()->unique()->values()->all();
        if (!$sort_attribute_id) {
            return $all_ids;
        }

        // sort
        $sorted_ids = $this->getUserIds($sort_attribute_id, $sort_order);
        // 处理超出allUserIds范围的userId
        $intersected_ids = array_intersect($sorted_ids, $all_ids);
        return array_values(array_unique(array_merge($intersected_ids, $all_ids)));
    }

    /**
     * search by keyword
     * 
     * @param  string  $keyword
     * @return Collection[UserAttribute]
     */
    public function search($keyword)
    {
        $attribute_ids = UserAttribute::where('context', $this->context)->pluck('id')->all();
        if (!$attribute_ids) {
            return collect();
        }

        return UserValue::whereIn('attribute_id', $attribute_ids)
            ->where('value', 'like', "%{$keyword}%")
            ->get();
    }
}
