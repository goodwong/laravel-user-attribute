<?php

namespace Goodwong\UserValue;

use Illuminate\Support\Facades\Route;

class Router
{
    /**
     * attribute
     * 
     * @return void
     */
    public static function attribute()
    {
        Route::namespace('Goodwong\UserValue\Http\Controllers')->group(function () {
            Route::resource('user-attributes', 'UserAttributeController');
        });
    }

    /**
     * attribute group
     * 
     * @return void
     */
    public static function attributeGroup()
    {
        Route::namespace('Goodwong\UserValue\Http\Controllers')->group(function () {
            Route::resource('user-attribute-groups', 'UserAttributeGroupController');
        });
    }

    /**
     * user value
     * 
     * @return void
     */
    public static function userValue()
    {
        Route::namespace('Goodwong\UserValue\Http\Controllers')->group(function () {
            Route::resource('user-values', 'UserValueController')->only('index', 'store', 'destroy');
        });
    }

    /**
     * user id list
     * 
     * @return void
     */
    public static function userIdList()
    {
        Route::namespace('Goodwong\UserValue\Http\Controllers')->group(function () {
            Route::resource('user-ids', 'UserIdController')->only('index');;
        });
    }
}
