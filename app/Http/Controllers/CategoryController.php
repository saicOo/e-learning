<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
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
            return response()->json([
                'status' => true,
                'data' => [
                    'categories' => $categories,
                ]
            ], 200);
    }
}
