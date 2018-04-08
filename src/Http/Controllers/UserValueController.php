<?php

namespace Goodwong\UserValue\Http\Controllers;

use Goodwong\UserValue\Entities\UserValue as UserValueEntity;
use Goodwong\UserValue\UserValue;
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
            abort('功能未完成……', 403);
        }
        // 多个用户数据（数据矩阵）
        $user_ids = $request->input('users');
        $attribute_ids = $request->input('attributes');
        if ($user_ids && $attribute_ids) {
            return UserValue::context('_')
                ->attribute(explode(',', $attribute_ids))
                ->valuesOfMany(explode(',', $user_ids));
        }
        // 单个用户数据
        $user_id = $request->input('user');
        $attribute_ids = $request->input('attributes');
        if ($user_id && $attribute_ids) {
            return UserValue::user($user_id)
                ->attribute(explode(',', $attribute_ids))
                ->values();
        }
        // 单用户数据，按code搜索
        $user_id = $request->input('user');
        $context = $request->input('context');
        $codes = $request->input('codes');
        if ($user_id && $codes) {
            return UserValue::user($user_id)
                ->context($context)
                ->code(explode(',', $codes))
                ->values();
            // abort('功能未完成……', 403);
            // $values = array_map(function ($code) use ($user_id) {
            //     $code = explode(':', $code);
            //     if (count($code) < 2) {
            //         return null;
            //     }
            //     $value = (new UserDataHandler($code[0]))->getByCode($user_id, $code[1]);
            //     if ($value) {
            //         $value->attribute_code = implode(':', $code);
            //     }
            //     return $value;
            // }, (array)explode(',', $codes));
            // return array_filter($values);
        }
        // 数据历史
        $user_id = $request->input('user');
        $attribute_id = $request->input('attribute');
        if ($user_id && $attribute_id) {
            return UserValue::user($user_id)
                ->attribute($attribute_id)
                ->history()
                ->slice(0, 10);
        }
        // nothing
        return collect();
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
            UserValue::user($user_id)
                ->attribute($attribute_id)
                ->value($value);
            return response('ok');
        }
        // by context & code
        $user_id = $request->input('user');
        $context = $request->input('context');
        $code = $request->input('code');
        $value = $request->input('value');
        if ($user_id && $context && $code) {
            UserValue::user($user_id)
                ->context($context)
                ->code($code)
                ->value($value);
            return response()->json('ok');
        }
        abort(422);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        UserValueEntity::find($id)->delete();
        return response('ok', 204);
    }
}
