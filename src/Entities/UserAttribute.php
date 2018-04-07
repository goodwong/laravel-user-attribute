<?php

namespace Goodwong\UserValue\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Goodwong\DefaultJsonField\Traits\DefaultJsonField;

class UserAttribute extends Model
{
    use SoftDeletes;
    use DefaultJsonField;

    /**
     * table name
     */
    protected $table = 'user_attributes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'context',
        'group_id',
        'label',
        'type',
        'code',
        'settings',
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

    /**
     * cast attributes
     */
    protected $casts = [
        'settings' => 'object',
    ];

    /**
     * The default settings.
     * 注意：这里只能是一级数组
     *
     * @var array
     */
    protected $default_settings = [
        //
    ];
}
