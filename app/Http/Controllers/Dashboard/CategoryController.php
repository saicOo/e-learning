<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\BaseController as BaseController;

class CategoryController extends BaseController
{
    public function __construct()
    {
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
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     * )
     */
    public function index(Request $request)
    {

        $categories = Category::query();

        $categories->with(['level:id,name']);

        // Filter by course name
        if ($request->has('search')) {
            $categories->where('name', 'like', '%' . $request->input('search') . '%');
        }
        if($request->has('level_id')){
            $categories->where('level_id', $request->input('level_id'));
        }

        $categories = $categories->withCount(['courses'])->latest('created_at')->get();

        return $this->sendResponse("",['categories' => $categories]);
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
     *             @OA\Property(property="level_id", type="integer", example="integer"),
     *
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
             'level_id'=> 'required|exists:levels,id',
         ]);

         if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
         }

         $category = Category::create($validate->validated());
         return $this->sendResponse("Category Created Successfully");
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
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $category->update($validate->validated());

        return $this->sendResponse("Category Updated Successfully",['category' => $category]);
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
        return $this->sendResponse("Deleted Data Successfully");
    }
}
