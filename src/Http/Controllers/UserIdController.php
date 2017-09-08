<?php

namespace Goodwong\LaravelUserAttribute\Http\Controllers;

use Goodwong\LaravelUserAttribute\Entities\UserAttribute;
use Goodwong\LaravelUserAttribute\Handlers\UserDataHandler;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class UserIdController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // 按属性
        $attribute_id = $request->input('attribute_id');
        if ($attribute_id) {
            $attribute = UserAttribute::findOrFail($attribute_id);
            $handler = new UserDataHandler($attribute->context);
            $user_ids = $handler->getUserIds($attribute_id, $request->input('sort_direction'));

            return $this->paginate($user_ids, $request);
        }
        // 按场景
        $context = $request->input('context');
        if ($context) {
            $handler = new UserDataHandler($context);
            $user_ids = $handler->allUserIds($request->input('sort_attribute_id'), $request->input('sort_direction', 'desc'));

            return $this->paginate($user_ids, $request);
        }
        // nothing
        return collect();
    }

    /**
     * paginate
     * 
     * @param  array | collection  $data
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    private function paginate($data, Request $request)
    {
        $per_page = $request->input('per_page', 20);
        $chunks = array_chunk($data, $per_page);
        $current_page = $request->input('page', 1);
        $current_page = max(0, min($current_page, count($chunks) - 1));
        $slice = $chunks[$current_page];
        return new LengthAwarePaginator($slice, count($data), $per_page, $current_page);
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  integer  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  integer  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  integer  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  integer  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
