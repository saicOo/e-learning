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
    public function __construct()
    {
        $this->middleware(['permission:listens_read'])->only(['index','show']);
        $this->middleware(['permission:listens_create'])->only('store');
        $this->middleware(['permission:listens_update'])->only('update');
        $this->middleware(['permission:listens_delete'])->only('destroy');
        $this->middleware(['permission:listens_approve'])->only('approve');
    }
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
     *             @OA\Property(property="video", type="file", example="string"),
     *             @OA\Property(property="attached", type="file", example="string"),
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
            'video_type' => 'required|in:file,url',
            'video' => 'required|mimes:mp4|max:8192',
            'attached' => 'nullable|mimes:pdf|max:8192',
            'course_id'=> 'required|exists:courses,id'
        ]); // notes validate video_type


        if($validate->fails()){
            return response()->json([
                'success' => false,
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'validation error',
                'errors' => $validate->errors()
            ], 200);
        }

        $request_data = $validate->validated();

        $path_video = $request->file('video')->store('video',['disk' => 'public']);
        $request_data['video'] = $path_video;

        if($request->attached){
            $path_attached = $request->file('attached')->store('attached',['disk' => 'public']);
            $request_data['attached'] = $path_attached;
        }

        $listen = Listen::create($request_data);

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
     *             @OA\Property(property="video", type="file", example="path file"),
     *             @OA\Property(property="attached", type="file", example="path file"),
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
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
            'video' => 'nullable|mimes:mp4|max:8192',
            'attached' => 'nullable|mimes:pdf|max:8192',
            'course_id'=> 'nullable|exists:courses,id',
        ]);

        if($validate->fails()){
            return response()->json([
                'success' => false,
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'validation error',
                'errors' => $validate->errors()
            ], 200);
        }

        $request_data = $validate->validated();

        if($request->video){
            if($listen->video != 'video/zNAS2X0zOi3RsC58jRqVf5gqmEodZl2DeYEsbGhr.mp4'){
                Storage::disk('public')->delete($listen->video);
            }
            $path_video = $request->file('video')->store('video',['disk' => 'public']);
            $request_data['video'] = $path_video;
        }

        if($request->attached){
            if($listen->attached != 'attached/hasjhRZGDGT8ptnIBfyo4voFTFHvcOsnr5FRSlJA.pdf'){
                Storage::disk('public')->delete($listen->attached);
            }
            $path_attached = $request->file('attached')->store('attached',['disk' => 'public']);
            $request_data['attached'] = $path_attached;
        }

        $request_data['active'] = 0;
        $listen->update($request_data);

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
        if($listen->video != 'video/zNAS2X0zOi3RsC58jRqVf5gqmEodZl2DeYEsbGhr.mp4'){
            Storage::disk('public')->delete($listen->video);
        }
        if($listen->attached != 'attached/hasjhRZGDGT8ptnIBfyo4voFTFHvcOsnr5FRSlJA.pdf'){
            Storage::disk('public')->delete($listen->attached);
        }
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
