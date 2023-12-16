<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:categories_read'])->only('index');
        $this->middleware(['permission:categories_create'])->only('store');
        $this->middleware(['permission:categories_update'])->only('update');
        $this->middleware(['permission:categories_delete'])->only('destroy');
    }
    /**
     * @OA\Get(
     *     path="/api/dashboard/categories",
     *      tags={"Dashboard Api Categories"},
     *     summary="get all Category",
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
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     * )
     */
    public function index(Request $request)
    {
        $categories = Category::when($request->search,function ($query) use ($request){ // if search
            return $query->where('name','Like','%'.$request->search.'%');
        })->get();

            return response()->json([
                'status' => true,
                'data' => [
                    'categories' => $categories,
                ]
            ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/dashboard/categories",
     *      tags={"Dashboard Api Categories"},
     *     summary="Add New Category",
     * @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="string"),
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     * )
     */
    public function store(Request $request)
    {
         //Validated
         $validate = Validator::make($request->all(),
         [
             'name' => 'required|string|max:255',
         ]);


         if($validate->fails()){
             return response()->json([
                 'success' => false,
                 'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                 'message' => 'validation error',
                 'errors' => $validate->errors()
             ], 200);
         }

         $category = Category::create($validate->validated());

         return response()->json([
             'status' => true,
             'message' => 'Category Created Successfully',
         ], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/dashboard/categories/{category_id}",
     *      tags={"Dashboard Api Categories"},
     *     summary="Updated Category",
     * @OA\Parameter(
     *          name="category_id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     * @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="string"),
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     * )
     */
    public function update(Request $request, Category $category)
    {
        //Validated
        $validate = Validator::make($request->all(),
        [
            'name' => 'required|string|max:255',
        ]);


        if($validate->fails()){
            return response()->json([
                'success' => false,
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'validation error',
                'errors' => $validate->errors()
            ], 200);
        }

        $category->update([
            'name'=>$request->name,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Category Updated Successfully',
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/dashboard/categories/{category_id}",
     *      tags={"Dashboard Api Categories"},
     *     summary="Delete Category",
     *     @OA\Parameter(
     *         name="category_id",
     *         in="path",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *       @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=404, description="Resource Not Found")
     *    )
     */
    public function destroy(Category $category)
    {
        $category->delete();
            return response()->json([
                'status' => true,
                'message' => 'Deleted Data Successfully',
            ], 200);
    }
}
