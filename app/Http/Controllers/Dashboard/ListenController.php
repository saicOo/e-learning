<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Listen;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Support\Facades\Validator;

class ListenController extends BaseController
{
    public function __construct()
    {
        $this->middleware(['permission:listens_read'])->only(['index','show']);
        $this->middleware(['permission:listens_create'])->only('store');
        $this->middleware(['permission:listens_update'])->only('update');
        $this->middleware(['permission:listens_update'])->only('uploadVideo');
        $this->middleware(['permission:listens_update'])->only('uploadFile');
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

        return $this->sendResponse("",['listens' => $listens]);
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
            'course_id'=> 'required|exists:courses,id'
        ]);


        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $request_data = $validate->validated();

        $listen = Listen::create($request_data);

        return $this->sendResponse("Listen Created Successfully",['listen' => $listen]);
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
        return $this->sendResponse("",['listen' => $listen]);
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
            'course_id'=> 'nullable|exists:courses,id',
        ]);

        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $request_data = $validate->validated();

        $request_data['active'] = 0;
        $listen->update($request_data);

        return $this->sendResponse("Listen Updated Successfully",['listen' => $listen]);
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
        return $this->sendResponse("Deleted Data Successfully");
    }

    /**
     * @OA\Put(
     *     path="/api/dashboard/listens/{listen_id}/upload-video",
     *      tags={"Dashboard Api Listens"},
     *     summary="upload video Listen",
     *     @OA\Parameter(
     *         name="listen_id",
     *         in="path",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     * @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="video", type="file", example="path file"),
     *         ),
     *     ),
     *       @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=404, description="Resource Not Found")
     *    )
     */
    public function uploadVideo(Request $request, Listen $listen)
    {
        if($request->video_type == 'file'){
            $validate = Validator::make($request->all(),
            [
                'video' => 'required|mimes:mp4|max:8192',
                'video_type' => 'required|in:file',
            ]);
        }else{
            $validate = Validator::make($request->all(),
            [
                'video' => 'required|url|max:1000',
                'video_type' => 'required|in:url',
            ]);
        }

        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if($listen->video != 'video/zNAS2X0zOi3RsC58jRqVf5gqmEodZl2DeYEsbGhr.mp4' || $listen->video != null){
            Storage::disk('public')->delete($listen->video);
        }
        $path_video = $request->file('video')->store('video',['disk' => 'public']);

        $listen->update([
            'active'=> 0,
            'video'=> $path_video,
        ]);

        return $this->sendResponse("The video has been uploaded successfully",['listen' => $listen]);
    }

    /**
     * @OA\Post(
     *     path="/api/dashboard/listens/{listen_id}/upload-file",
     *      tags={"Dashboard Api Listens"},
     *     summary="upload file Listen",
     *     @OA\Parameter(
     *         name="listen_id",
     *         in="path",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     * @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="file", type="file", example="path file"),
     *         ),
     *     ),
     *       @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=404, description="Resource Not Found")
     *    )
     */
    public function uploadFile(Request $request, Listen $listen)
    {
        //Validated
        $validate = Validator::make($request->all(),
        [
            'attached' => 'required|mimes:pdf|max:8192',
        ]);

        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

            if($listen->attached != 'attached/hasjhRZGDGT8ptnIBfyo4voFTFHvcOsnr5FRSlJA.pdf' || $listen->attached != null){
                Storage::disk('public')->delete($listen->attached);
            }
            $path_attached = $request->file('attached')->store('attached',['disk' => 'public']);


        $listen->update([
            'active'=> 0,
            'attached'=> $path_attached,
        ]);
        return $this->sendResponse("The file has been uploaded successfully",['listen' => $listen]);
    }

    /**
     * @OA\Put(
     *     path="/api/dashboard/listens/{listen_id}/approve",
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
     * @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="active", type="boolen", example="1 or 0"),
     *         ),
     *     ),
     *       @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=404, description="Resource Not Found")
     *    )
     */
    public function approve(Request $request, Listen $listen)
    {
        //Validated
        $validate = Validator::make($request->all(),
        [
            'active' => 'required|in:1,0',
        ]);

        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $listen->update([
            'active'=> $request->active,
        ]);
        
        return $this->sendResponse("Listen Approved Successfully",['listen' => $listen]);
    }
}
