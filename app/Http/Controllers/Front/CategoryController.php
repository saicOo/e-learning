<?php

namespace App\Http\Controllers\Front;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController as BaseController;

class CategoryController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/categories",
     *      tags={"Front Api Categories"},
     *     summary="Get All Categories",
     * @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="filter search name categories",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     * @OA\Parameter(
     *         name="level_id",
     *         in="query",
     *         description="filter categories with level",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *       @OA\Response(response=200, description="OK"),
     *    )
     */
    public function index(Request $request)
    {
        $categories = Category::query();

        $categories->with(['level:id,name']);

        if($request->has('level_id')){
            $categories->where('level_id', $request->input('level_id'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $categories->where(function($query) use ($search) {
                  $query->where('name', 'LIKE', '%'.$search.'%')
                    ->orWhereHas('level', function($q) use ($search){
                        $q->where('name', 'LIKE', '%' . $search . '%');
                    });
            });
        }

        $categories = $categories->withCount(['courses'])->latest('created_at')->get();
        return $this->sendResponse("",['categories' => $categories]);
    }
}
