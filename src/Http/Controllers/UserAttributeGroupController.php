<?php

namespace Goodwong\LaravelUserAttribute\Http\Controllers;

use Goodwong\LaravelUserAttribute\Entities\UserAttributeGroup;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserAttributeGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = UserAttributeGroup::orderBy('id', 'desc');
        if ($request->has('context')) {
            $query->where('context', $request->input('context'));
        }
        return $query->get();
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
        return UserAttributeGroup::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \Goodwong\LaravelUserAttribute\Entities\UserAttributeGroup  $userAttributeGroup
     * @return \Illuminate\Http\Response
     */
    public function show(UserAttributeGroup $userAttributeGroup)
    {
        return $userAttributeGroup;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Goodwong\LaravelUserAttribute\Entities\UserAttributeGroup  $userAttributeGroup
     * @return \Illuminate\Http\Response
     */
    public function edit(UserAttributeGroup $userAttributeGroup)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Goodwong\LaravelUserAttribute\Entities\UserAttributeGroup  $userAttributeGroup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UserAttributeGroup $userAttributeGroup)
    {
        $userAttributeGroup->update($request->all());
        return $userAttributeGroup;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Goodwong\LaravelUserAttribute\Entities\UserAttributeGroup  $userAttributeGroup
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserAttributeGroup $userAttributeGroup)
    {
        $userAttributeGroup->delete();
        return response()->json(null, 204);
    }
}
