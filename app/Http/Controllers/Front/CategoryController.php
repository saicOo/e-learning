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
     *       @OA\Response(response=200, description="OK"),
     *    )
     */
    public function index()
    {
        $categories = Category::all();
        return $this->sendResponse("",['categories' => $categories]);
    }
}
