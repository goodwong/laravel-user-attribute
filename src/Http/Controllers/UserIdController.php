<?php

namespace Goodwong\UserValue\Http\Controllers;

use Goodwong\UserValue\UserValue;
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
        $attribute_id = $request->input('attribute');
        if ($attribute_id) {
            abort(403, '功能未完成……');
            return $this->paginate($user_ids, $request);
        }
        // filters
        $filters = $request->input('filters');
        $sort_attribute = $request->input('sort_attribute');
        $sort_order = $request->input('sort_order', 'desc');
        if ($filters) {
            abort(403, '功能未完成……');
            return $this->paginate($user_ids, $request);
        }
        // 按场景
        $context = $request->input('context');
        if ($context) {
            $user_ids = UserValue::context($context)->users();
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
        $current_page = max(1, min($current_page, count($chunks)));
        $slice = data_get($chunks, $current_page - 1, []);
        return new LengthAwarePaginator($slice, count($data), $per_page, $current_page);
    }
}
