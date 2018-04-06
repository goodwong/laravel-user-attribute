<?php

namespace Goodwong\UserAttribute\Handlers;

use Exception;
use Goodwong\UserAttribute\Entities\UserAttributeGroup;
use Goodwong\UserAttribute\Entities\UserAttribute;
use Goodwong\UserAttribute\Entities\UserValue;

class UserValueHandler
{
    /**
     * @property string $context
     */
    private $context;

    /**
     * @property int|array $attribute
     */
    private $attribute;

    /**
     * @property int $sort_attribute 排序属性、方向、类型BY_VALUE/BY_TIME
     * e.g. {attribute:28, direction:'ASC', type:'BY_VALUE'}
     */
    private $sort;

    /**
     * @property array $date_limit 日期范围-开始（结束）
     * e.g. ['2018-3-5'] | ['2018-3-5', '2018-3-20']
     */
    private $date_limit;

    /**
     * @property array $filters
     * e.g. [{attribute:15, options:['红色', '绿色']}]
     */
    private $filters = [];

    /**
     * set context
     * 
     * @param  string  $context
     * @return self
     */
    public function context(string $context)
    {
        $this->context = $context;
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
        $this->loadAttributeByCode($code);
        return $this;
    }

    /**
     * load or create attribute by code
     * 
     * @param  string|array  $code
     * @return void
     */
    private function loadAttributeByCode ($code)
    {
        if (is_array($code)) {
            // 多个属性
            $attribute = $this->attributeModel()->whereIn('code', $code)->pluck('id')->all();
            $this->attribute($attribute);
        } else {
            // 单个属性
            $attribute = $this->attributeModel()->where('code', $code)->value('id');
            $this->attribute($attribute);
        }
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
     * search
     * 
     * @return Collection 返回：[{ user_id, reviser_id, attribute_id, value, created_at }]
     */
    public function search ()
    {
        $this->require('context');
        $this->require('attribute');
        $this->clearup();
        return;
    }

    /**
     * values of many
     * 
     * @param  array  $userIds
     * @return Collection
     */
    public function valuesOfMany ($userIds)
    {
        $this->require('context');
        $this->require('attribute');
        $query = $this->valueModel();
        if (is_array($this->attribute)) {
            $query = $query->whereIn('attribute_id', $this->attribute);
        } else {
            $query = $query->where('attribute_id', $this->attribute);
        }
        $query = $query->whereIn('user_id', $userIds);
        $this->clearup();
        return $query->get(['user_id', 'reviser_id', 'attribute_id', 'value', 'created_at'])
            ->each(function ($v) {
                $v->value = unserialize($v->value);
            });
    }

    /**
     * all user ids
     * 
     * @return array 用户id列表
     */
    public function users ()
    {
        /**
         * 1. all ids / attribute ids, limit date
         * 2. 并集 sorted ids
         * 3. 交集 filtered ids
         */

        /**
         * 1. all ids / attribute ids, limit date
         */
        $this->require('context');
        $query = $this->valueModel();
        if ($this->attribute) {
            if (is_array($this->attribute)) {
                throw new Exception("multiple attributes is not supported!");
            }
            $query = $query->where('attribute_id', $this->attribute);
        } else {
            $attributeIds = $this->attributeModel()->pluck('id')->all();
            if (!$attributeIds) {
                return [];
            }
            $query = $query->whereIn('attribute_id', $attributeIds);
        }
        // date limit
        $all = [];
        if ($this->date_limit) {
            $query = $query->where('created_at', '>=', $this->date_limit[0]);
        }
        if (isset($this->date_limit[1])) {
            $query = $query->where('created_at', '<', $this->date_limit[1]);
        }
        $all = $query->pluck('user_id')->all();

        /**
         * 2. 并集 sorted ids
         */
        if ($this->sort) {
            $query = $this->valueModel();
            if ($this->attribute) {
                $query = $query->where('attribute_id', $this->attribute);
            } else {
                $query = $query->where('attribute_id', $this->sort['attribute']);
            }
            if ($this->sort['type'] === 'BY_TIME') {
                $query = $query->orderBy('created_at', 'asc');
            }
            $sorted = $query->pluck('value', 'user_id');
            if ($this->sort['type'] === 'BY_VALUE') {
                $sorted = $sorted->map(function ($v) {
                    return unserialize($v);
                })->sort(function ($a, $b) {
                    if (gettype($a) === 'array') {
                        $a = implode(',', $a);
                    }
                    if (gettype($a) === 'object') {
                        $a = implode(',', (array)$a);
                    }
                    if (gettype($b) === 'array') {
                        $b = implode(',', $b);
                    }
                    if (gettype($b) === 'object') {
                        $b = implode(',', (array)$b);
                    }
                    return $a === $b ? 0 : ($a < $b ? -1 : 1);
                });
            }
            $sorted = $sorted->keys()->all();
            if ($this->sort['direction'] === 'DESC') {
                $sorted = array_reverse($sorted);
            }
            $all = array_values(array_merge($sorted, $all));
        }

        /**
         * 3. 交集 filtered ids
         */
        if ($this->filters) {
            foreach ($this->filters as $filter) {
                $filterFinds = [];
                foreach ($filter['options'] as $option) {
                    $optionMatcheds = $this->valueModel()
                        ->where('attribute_id', $filter['attribute'])
                        ->where('value', 'like', '%"'.$option.'"%')
                        ->pluck('user_id')
                        ->all();
                    $filterFinds = array_merge($filterFinds, $optionMatcheds);
                }
                $all = array_intersect($all, $filterFinds); // 交集
            }
        }
        $this->clearup();
        return array_values(array_unique($all));
    }

    /**
     * get value model
     * 
     * @return Builder
     */
    private function valueModel ()
    {
        return UserValue::getModel();
    }

    /**
     * date limit
     * 
     * @param  string  $rangeStart
     * @param  string  $rangeEnd
     * @return self
     */
    public function dateRange($rangeStart, $rangeEnd = null)
    {
        $this->date_limit = [$rangeStart];
        if ($rangeEnd) {
            array_push($this->date_limit, $rangeEnd);
        }
        return $this;
    }

    /**
     * sort by time
     * 
     * @param  int|string  $code / $direction 当为单属性
     * @param  string  $direction
     * @return self
     */
    public function sortByTime ()
    {
        if (func_num_args() === 2) {
            $code = func_get_arg(0);
            $direction = func_get_arg(1);
            $attribute = $this->codeToAttribute($code);
        } elseif (func_num_args() === 1) {
            $direction = func_get_arg(0);
        }
        $this->sort = ['attribute' => $attribute ?? null, 'direction' => strtoupper($direction), 'type' => 'BY_TIME'];
        return $this;
    }

    /**
     * sort by value
     * 
     * @param  int|string  $code
     * @param  string  $direction
     * @return self
     */
    public function sortByValue ()
    {
        if (func_num_args() === 2) {
            $code = func_get_arg(0);
            $direction = func_get_arg(1);
            $attribute = $this->codeToAttribute($code);
        } elseif (func_num_args() === 1) {
            $direction = func_get_arg(0);
        }
        $this->sort = ['attribute' => $attribute ?? null, 'direction' => strtoupper($direction), 'type' => 'BY_VALUE'];
        return $this;
    }

    /**
     * add filter
     * 
     * @param  int|string  $code
     * @param  string|array  $option
     * @return self
     */
    public function filter ($code, $option)
    {
        $attribute = $this->codeToAttribute($code);
        $options = is_array($option) ? $option : [$option];
        array_push($this->filters, ['attribute' => $attribute, 'options' => $options]);
        return $this;
    }

    /**
     * code to attribute
     * 
     * @param  string|int  $code
     * @return int
     */
    private function codeToAttribute ($code)
    {
        if (gettype($code) === 'integer') {
            return $code;
        }
        $attribute = $this->attributeModel()->where('code', $code)->value('id');
        return $attribute;
    }

    /**
     * clear up
     * 
     * @return void
     */
    private function clearup ()
    {
        $this->attribute = null;
        $this->filters = [];
        $this->sort = null;
        $this->date_limit = null;
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
            throw new Exception("require {$field}, field value missing!");
        }
    }

}
