<?php

namespace Goodwong\UserValue\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserAttributeGroup extends Model
{
    use SoftDeletes;

    /**
     * table name
     */
    protected $table = 'user_attribute_groups';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'context',
        'label',
        'position',
    ];

    /**
     * 在数组中想要隐藏的属性。
     *
     * @var array
     */
    protected $hidden = [];
    
    /**
     * date
     */
    protected $dates = [
        'deleted_at',
    ];
}
