<?php

namespace Goodwong\UserValue\Handlers;

use Exception;
use Goodwong\UserValue\Entities\UserAttributeGroup;
use Goodwong\UserValue\Entities\UserAttribute;
use Goodwong\UserValue\Entities\UserValue;

class UserValueDecorator
{
    /**
     * @var int $reviser 当前操作者，默认当前session登陆用户
     */
    private $reviser;

    /**
     * @var int $user 当前用户
     */
    private $user;

    /**
     * @var string $context 当前场景
     */
    private $context = 'default';

    /**
     * @var string $groupName 当前分组名称
     */
    private $groupName;

    /**
     * @var int $group 当前分组ID
     */
    private $group;

    /**
     * @var int|array $attribute
     */
    private $attribute;

    /**
     * @var string|array $code
     */
    private $code;

    /**
     * @var string $label
     */
    private $label;

    /**
     * @var string $type
     */
    private $type;

    /**
     * instance
     * 
     * @param  int  $user
     * @return self
     */
    public function __construct (int $user)
    {
        $this->user = $user;
        if (request()->user()) {
            $this->reviser(request()->user()->id);
        }
    }

    /**
     * set reviser
     * 
     * @param  int  $reviser
     * @return self
     */
    public function reviser (int $reviser)
    {
        $this->reviser = $reviser;
        return $this;
    }

    /**
     * set context
     * 
     * @param  string  $context
     * @return self
     */
    public function context (string $context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * set group
     * 
     * @param  string  $group
     * @return self
     */
    public function group (string $group)
    {
        $this->groupName = $group;
        return $this;
    }

    /**
     * set attribute
     * 
     * @param  int|array  $attribute
     * @return self
     */
    public function attribute ($attribute)
    {
        $this->attribute = $attribute;
        return $this;
    }

    /**
     * set code
     * 
     * @param  string|array  $code
     * @return self
     */
    public function code ($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * set label
     * 
     * @param  string  $label
     * @return self
     */
    public function label (string $label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * set type
     * 
     * @param  string  $type
     * @return self
     */
    public function type (string $type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * set/get value
     * 
     * @param  mixed  $value
     * @param  bool  $forceWrite
     * @return self|mixed
     */
    public function value ($value = null, bool $forceWrite = false)
    {
        if ($value !== null) {
            // 设置
            $this->setValue($value, $forceWrite);
            // 清空属性
            $this->attribute = null;
            return $this;
        } else {
            // 读取
            $value = $this->getValue();
            // 清空属性
            $this->attribute = null;
            return $value;
        }
    }

    /**
     * set value
     * 
     * @param  mixed  $value
     * @param  bool  $forceWrite
     * @return void
     */
    private function setValue ($value, $forceWrite = false)
    {
        // 检查依赖参数
        if ($this->code) {
            $this->loadAttributeByCode();
        }
        $this->require('attribute');

        // 保存数据
        $value = serialize($value);
        $exist = $this->valueModel()->where('attribute_id', $this->attribute)->first();
        if ($exist && $exist->value === $value && !$forceWrite) {
            return;
        }
        if ($exist) {
            $exist->delete();
        }
        $this->valueModel()->create([
            'user_id' => $this->user,
            'attribute_id' => $this->attribute,
            'reviser_id' => $this->reviser,
            'value' => $value,
        ]);
        return;
    }

    /**
     * load or create attribute by code
     * 
     * @return void
     */
    private function loadAttributeByCode ()
    {
        $this->require('context');
        $this->require('code');
        if (is_array($this->code)) {
            $mapped = $this->attributeModel()->whereIn('code', $this->code)
                ->pluck('id', 'code');
            foreach ($this->code as $code) {
                $attribute[$code] = $mapped[$code] ?? null;
            }
            $this->attribute($attribute);
            // 多个属性，用于读取，就不需要创建不存在的属性了

        } else {
            // 单个属性

            // 查找
            $attribute = $this->attributeModel()->where('code', $this->code)->value('id');
            if ($attribute) {
                return $this->attribute($attribute);
            }

            // 创建
            $group = $this->getGroup();
            $attribute = UserAttribute::create([
                'context' => $this->context, 
                'group_id' => $group, 
                'label' => $this->label ?? $this->code,
                'code' => $this->code,
                'type' => $this->type ?? 'input.text',
            ]);
            $this->attribute($attribute->id);
        }
        // 清理
        $this->code = null;
        $this->label = null;
        $this->type = null;
    }

    /**
     * get or create attribute group
     * 
     * @return int 分类id
     */
    private function getGroup ()
    {
        // 有缓存，且无需切换
        if ($this->group && !$this->groupName) {
            return $this->group;
        }
        // 查找／创建
        $group = $this->groupModel()->firstOrCreate([
            'label' => $this->groupName ?: '默认',
            'context' => $this->context,
        ]);
        // 缓存
        $this->groupName = null;
        $this->group = $group->id;
        return $this->group;
    }

    /**
     * get attribute model
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function attributeModel ()
    {
        $this->require('context');
        return UserAttribute::getModel()->where('context', $this->context);
    }

    /**
     * get group model
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function groupModel ()
    {
        $this->require('context');
        return UserAttributeGroup::getModel()->where('context', $this->context);
    }

    /**
     * get value
     * 
     * @return mixed
     */
    private function getValue ()
    {
        // 检查依赖参数
        if ($this->code) {
            $this->loadAttributeByCode();
        }
        $this->require('attribute');

        // 查找
        $value = $this->valueModel()->where('attribute_id', $this->attribute)->value('value');
        return $value ? unserialize($value) : null;
    }

    /**
     * get value model
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function valueModel ()
    {
        return UserValue::getModel()->where('user_id', $this->user);
    }

    /**
     * increase
     * 
     * @param  int  $augend
     * @return self
     */
    public function increase (int $augend = 1)
    {
        $this->increaseAndGet($augend);
        return $this;
    }

    /**
     * increase and get value
     * 
     * @param  int  $augend
     * @return int
     */
    public function increaseAndGet (int $augend = 1)
    {
        $old = $this->getValue();
        if (!$old) {
            $old = 0;
        }
        $value = $old + $augend;
        $this->setValue($value, true); // 强制写入，万一出现竞态问题，留下数据便于修复
        // 清空属性
        $this->attribute = null;
        return $value;
    }

    /**
     * add item to set
     * 
     * @param  string|array  $item
     * @return self
     */
    public function add ($item)
    {
        $this->addAndGet($item);
        return $this;
    }

    /**
     * add item to set and get value
     * 
     * @param  string|array  $item
     * @return array
     */
    public function addAndGet ($item)
    {
        $old = $this->getValue();
        if (!$old) {
            $old = [];
        }
        $items = is_array($item) ? $item : [$item];
        $items = array_merge($old, $items);
        $items = array_map(function ($v) { return trim($v); }, $items);
        sort($items);
        $value = array_unique($items);
        $this->setValue($value);
        // 清空属性
        $this->attribute = null;
        return $value;
    }

    /**
     * empty
     */
    public function empty ()
    {
        // 检查依赖参数
        if ($this->code) {
            $this->loadAttributeByCode();
        }
        $this->require('attribute');
        // 删除
        $this->valueModel()->where('attribute_id', $this->attribute)->delete();
        // 清空属性
        $this->attribute = null;

        return $this;
    }

    /**
     * get values
     * 
     * @return array
     */
    public function values ()
    {
        // 检查依赖参数
        $byCode = $this->code;
        if ($this->code && is_array($this->code)) {
            $this->loadAttributeByCode();
        }
        $this->require('attribute');
        if (!is_array($this->attribute)) {
            throw new Exception("attribute should be an array, integer found.");
        }

        // 查询数据
        if (array_filter($this->attribute)) {
            $values = $this->valueModel()->whereIn('attribute_id', array_filter($this->attribute))
                ->pluck('value', 'attribute_id');
        } else {
            $values = [];
        }
        foreach ($this->attribute as $code => $attribute) {
            $value = isset($values[$attribute]) ? unserialize($values[$attribute]) : null;
            if ($byCode) {
                $mappedValues[$code] = $value;
            } else {
                $mappedValues[$attribute] = $value;
            }
        }

        // 清空属性
        $this->attribute = null;
        return $mappedValues;
    }

    /**
     * history
     * 
     * @return Collection;
     */
    public function history ()
    {
        // 检查依赖参数
        if ($this->code) {
            $this->loadAttributeByCode();
        }
        $this->require('attribute');

        // 查找
        $history = $this->valueModel()->withTrashed()->where('attribute_id', $this->attribute)->get();

        // 清空属性
        $this->attribute = null;
        return $history;
    }

    /**
     * require field
     * 
     * @param  string  $field
     * @throws \Exception
     */
    private function require (string $field)
    {
        if (!$this->$field) {
            throw new Exception("field `{$field}` missing!");
        }
    }
}
