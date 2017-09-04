<?php

namespace Goodwong\LaravelUserAttribute\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserValue extends Model
{
    use SoftDeletes;

    /**
     * table name
     */
    protected $table = 'user_values';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'attribute_id',
        'value',
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
