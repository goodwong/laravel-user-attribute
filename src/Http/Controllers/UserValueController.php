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
        $context = $request->input('context', null);
        $attributes = $request->input('attributes');
        $keyword = $request->input('keyword');
        if ($keyword) {
            $handler = new UserDataHandler($context);
            return $handler->search(explode(',', $attributes), $keyword);
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
        // 单用户数据，按code搜索
        $user_id = $request->input('user');
        $codes = $request->input('codes');
        if ($user_id && $codes) {
            $values = array_map(function ($code) use ($user_id) {
                $code = explode(':', $code);
                if (count($code) < 2) {
                    return null;
                }
                $value = (new UserDataHandler($code[0]))->getByCode($user_id, $code[1]);
                if ($value) {
                    $value->attribute_code = implode(':', $code);
                }
                return $value;
            }, (array)explode(',', $codes));
            return array_filter($values);
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
        // by context & code
        $user_id = $request->input('user');
        $context = $request->input('context');
        $code = $request->input('code');
        $value = $request->input('value');
        if ($user_id && $context && $code) {
            $handler = new UserDataHandler($context);
            return response()->json($handler->setByCode($user_id, $code, $value ?: ''));
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
