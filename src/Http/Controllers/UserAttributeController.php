<?php

namespace Goodwong\LaravelUserAttribute\Http\Controllers;

use Goodwong\LaravelUserAttribute\Entities\UserAttribute;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserAttributeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = UserAttribute::orderBy('id', 'desc');
        // 返回指定group_id列表的数据
        $group_ids = $request->input('groups');
        if ($group_ids) {
            return $query->whereIn('group_id', explode(',', $group_ids))->get();
        }
        // 单个context
        $context = $request->input('context');
        if ($context) {
            return $query->where('context', $context)->get();
        }
        // 多个context
        $contexts = $request->input('contexts');
        if ($contexts) {
            return $query->whereIn('context', explode(',', $contexts))->get();
        }
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
        return UserAttribute::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \Goodwong\LaravelUserAttribute\Entities\UserAttribute  $userAttribute
     * @return \Illuminate\Http\Response
     */
    public function show(UserAttribute $userAttribute)
    {
        return $userAttribute;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Goodwong\LaravelUserAttribute\Entities\UserAttribute  $userAttribute
     * @return \Illuminate\Http\Response
     */
    public function edit(UserAttribute $userAttribute)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Goodwong\LaravelUserAttribute\Entities\UserAttribute  $userAttribute
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UserAttribute $userAttribute)
    {
        $userAttribute->update($request->all());
        return $userAttribute;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Goodwong\LaravelUserAttribute\Entities\UserAttribute  $userAttribute
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserAttribute $userAttribute)
    {
        $userAttribute->delete();
        return response()->json(null, 204);
    }
}
