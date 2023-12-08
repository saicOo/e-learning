<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Listen;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class ListenController extends Controller
{
/**
     * @OA\Get(
     *     path="/api/dashboard/listens",
     *      tags={"Dashboard Api Listens"},
     *     summary="get all listens",
     *   @OA\Parameter(
     *         name="course_id",
     *         in="query",
     *         description="filter listens with course",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
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
     *     path="/api/dashboard/listens",
     *      tags={"Dashboard Api Listens"},
     *     summary="Add New Listens",
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
            'video' => 'required|mimes:mp4|max:8192',
            'course_id'=> 'required|exists:courses,id'
        ]);


        if($validate->fails()){
            return response()->json([
                'success' => false,
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'validation error',
                'errors' => $validate->errors()
            ], 200);
        }
        $path_video = $request->file('video')->store('apifile',['disk' => 'public']);
        $listen = Listen::create([
           'name'=>$request->name,
           'description'=>$request->description,
           'video'=>$path_video,
           'course_id'=>$request->course_id,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Listen Created Successfully',
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/listens/{listen_id}",
     *      tags={"Dashboard Api Listens"},
     *     summary="show listen",
     *     @OA\Parameter(
     *         name="listen_id",
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
     *     path="/api/dashboard/listens/{listen_id}",
     *      tags={"Dashboard Api Listens"},
     *     summary="Updated Listen",
     * @OA\Parameter(
     *          name="listen_id",
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
     *             @OA\Property(property="description", type="string", example="string"),
     *             @OA\Property(property="course_id", type="integer", example="integer"),
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=404, description="Resource Not Found")
     * )
     */
    public function update(Request $request, Listen $listen)
    {
        //Validated
        $validate = Validator::make($request->all(),
        [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            // 'video' => 'required|mimes:mp4|max:8192',
            'course_id'=> 'required|exists:courses,id',
            // 'active' => 'required|in:1,0',
        ]);

        if($validate->fails()){
            return response()->json([
                'success' => false,
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'validation error',
                'errors' => $validate->errors()
            ], 200);
        }

        $listen->update([
            'name'=>$request->name,
            'description'=>$request->description,
            // 'video'=>$path_video,
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
     *     path="/api/dashboard/listens/{listen_id}",
     *      tags={"Dashboard Api Listens"},
     *     summary="Delete Listen",
     *     @OA\Parameter(
     *         name="listen_id",
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
    public function destroy(Listen $listen)
    {
        Storage::disk('public')->delete($listen->video);
        $listen->delete();
            return response()->json([
                'status' => true,
                'message' => 'Deleted Data Successfully',
            ], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/dashboard/listens/{listene_id}/approve",
     *      tags={"Dashboard Api Listens"},
     *     summary="Approve Listen",
     *     @OA\Parameter(
     *         name="listen_id",
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
