<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Listen;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ListenController extends Controller
{
/**
     * @OA\Get(
     *     path="/api/dashboard/listens",
     *      tags={"Dashboard Api Listenes"},
     *     summary="get all listens",
     *   @OA\Parameter(
     *         name="course_id",
     *         in="query",
     *         description="filter listens with course",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             default="null",
     *             type="integer",
     *         ),
     *     ),
     * @OA\Parameter(
     *         name="active",
     *         in="query",
     *         description="filter listens with active (active = 1 , not active = 0)",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             default="0 , 1",
     *             type="string",
     *         ),
     *     ),
     * @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="filter search name or description",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             default="keyword",
     *             type="string",
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     * )
     */
    public function index(Request $request)
    {
        $listens = Listen::when($request->course_id,function ($query) use ($request){ // if course_id
            return $query->where('course_id',$request->course_id);
        })->when($request->active,function ($query) use ($request){ // if active
            return $query->where('active',$request->active);
        })->when($request->search,function ($query) use ($request){ // if search
            return $query->where('name','Like','%'.$request->search.'%')->OrWhere('description','Like','%'.$request->search.'%');
        })->get();

        return response()->json([
            'status' => true,
            'data' => [
                'listens' => $listens,
            ]
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/dashboard/listenes",
     *      tags={"Dashboard Api Listenes"},
     *     summary="Add New Listenes",
     * @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="string"),
     *             @OA\Property(property="description", type="string", example="string"),
     *             @OA\Property(property="video", type="string", example="path or url"),
     *             @OA\Property(property="course_id", type="integer", example="integer"),
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
            'description' => 'required|string|max:255',
            'video' => 'required|string|max:255',
            'course_id'=> 'required|exists:courses,id'
        ]);


        if($validate->fails()){
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validate->errors()
            ], 401);
        }

        $listen = Listen::create([
           'name'=>$request->name,
           'description'=>$request->description,
           'video'=>$request->video,
           'course_id'=>$request->course_id,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Listen Created Successfully',
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/listenes/{listene_id}",
     *      tags={"Dashboard Api Listenes"},
     *     summary="show listene",
     *     @OA\Parameter(
     *         name="listene_id",
     *         in="path",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *             default="1",
     *             type="integer",
     *         ),
     *     ),
     *       @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     *    )
     */
    public function show(Listen $listen)
    {
        return response()->json([
            'status' => true,
            'data' => [
                'listen' => $listen,
            ]
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/dashboard/listenes/{listene_id}",
     *      tags={"Dashboard Api Listenes"},
     *     summary="Updated Listene",
     * @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="string"),
     *             @OA\Property(property="description", type="string", example="string"),
     *             @OA\Property(property="video", type="string", example="path or url"),
     *             @OA\Property(property="course_id", type="integer", example="integer"),
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     * )
     */
    public function update(Request $request, Listen $listen)
    {
        //Validated
        $validate = Validator::make($request->all(),
        [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'video' => 'required|string|max:255',
            'course_id'=> 'required|exists:courses,id',
            // 'active' => 'required|in:1,0',
        ]);

        if($validate->fails()){
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validate->errors()
            ], 401);
        }

        $listen->update([
            'name'=>$request->name,
            'description'=>$request->description,
            'video'=>$request->video,
            'course_id'=>$request->course_id,
            'active'=> 0,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Listen Updated Successfully',
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/dashboard/listenes/{listene_id}",
     *      tags={"Dashboard Api Listenes"},
     *     summary="Delete Listene",
     *     @OA\Parameter(
     *         name="listene_id",
     *         in="path",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *             default="1",
     *             type="integer",
     *         ),
     *     ),
     *       @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     *    )
     */
    public function destroy(Listen $listen)
    {
        $listen->delete();
            return response()->json([
                'status' => true,
                'message' => 'Deleted Data Successfully',
            ], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/dashboard/listenes/{listene_id}/approve",
     *      tags={"Dashboard Api Listenes"},
     *     summary="Approve Listene",
     *     @OA\Parameter(
     *         name="listene_id",
     *         in="path",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *             default="1",
     *             type="integer",
     *         ),
     *     ),
     *       @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     *    )
     */
    public function approve(Listen $listen)
    {
        $listen->update([
            'active'=> 1,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Listen Approved Successfully',
        ], 200);
    }
}
