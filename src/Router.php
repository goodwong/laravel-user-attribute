<?php

namespace Goodwong\LaravelUserAttribute;

use Illuminate\Support\Facades\Route;

class Router
{
    /**
     * routes
     * 
     * @return void
     */
    public static function attribute()
    {
        Route::namespace('Goodwong\LaravelUserAttribute\Http\Controllers')->group(function () {
            Route::resource('user-attributes', 'UserAttributeController');
        });
    }
}