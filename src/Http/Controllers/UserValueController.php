<?php

namespace Goodwong\LaravelUserAttribute\Http\Controllers;

use Goodwong\LaravelUserAttribute\Entities\UserValue;
use Goodwong\LaravelUserAttribute\Handlers\UserDataHandler;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserValueController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // 搜索
        $keyword = $request->input('search');
        $context = $request->input('context');
        if ($keyword && $context) {
            $handler = new UserDataHandler($context);
            return $handler->search($keyword);
        }
        // 多个用户数据（数据矩阵）
        $user_ids = $request->input('users');
        $attribute_ids = $request->input('attributes');
        if ($user_ids && $attribute_ids) {
            $handler = new UserDataHandler();
            return $handler->valuesOfMany(explode(',', $user_ids), explode(',', $attribute_ids));
        }
        // 单个用户数据
        $user_id = $request->input('user');
        $attribute_ids = $request->input('attributes');
        if ($user_id && $attribute_ids) {
            $handler = new UserDataHandler();
            return $handler->values($user_id, explode(',', $attribute_ids));
        }
        // 数据历史
        $user_id = $request->input('user');
        $attribute_id = $request->input('attribute');
        if ($user_id && $attribute_id) {
            $handler = new UserDataHandler();
            return $handler->history($user_id, $attribute_id)->take(10);
        }
        // nothing
        return collect();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // 单个用户数据
        $user_id = $request->input('user');
        $attribute_id = $request->input('attribute');
        $value = $request->input('value');
        if ($user_id && $attribute_id) {
            $handler = new UserDataHandler();
            return response()->json($handler->set($user_id, $attribute_id, $value ?: ''));
        }
        abort(422);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Goodwong\LaravelUserAttribute\Entities\UserValue  $userValue
     * @return \Illuminate\Http\Response
     */
    public function show(UserValue $userValue)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Goodwong\LaravelUserAttribute\Entities\UserValue  $userValue
     * @return \Illuminate\Http\Response
     */
    public function edit(UserValue $userValue)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Goodwong\LaravelUserAttribute\Entities\UserValue  $userValue
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UserValue $userValue)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Goodwong\LaravelUserAttribute\Entities\UserValue  $userValue
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserValue $userValue)
    {
        //
    }
}
