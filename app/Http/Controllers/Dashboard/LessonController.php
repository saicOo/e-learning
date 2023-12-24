<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\BaseController as BaseController;

class LessonController extends BaseController
{
    public function __construct()
    {
        $this->middleware(['permission:lessons_read'])->only(['index','show']);
        $this->middleware(['permission:lessons_create'])->only('store');
        $this->middleware(['permission:lessons_update'])->only('update');
        $this->middleware(['permission:lessons_update'])->only('uploadVideo');
        $this->middleware(['permission:lessons_update'])->only('uploadFile');
        $this->middleware(['permission:lessons_delete'])->only('destroy');
        $this->middleware(['permission:lessons_approve'])->only('approve');
        $this->middleware(['checkApiAffiliation']);

    }
/**
     * @OA\Get(
     *     path="/api/dashboard/courses/{course_id}/lessons",
     *      tags={"Dashboard Api Lessons"},
     *     summary="get all lessons",
     *   @OA\Parameter(
     *         name="course_id",
     *         in="query",
     *         description="filter lessons with course",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     * @OA\Parameter(
     *         name="publish",
     *         in="query",
     *         description="filter lessons with publish (publish , unpublish)",
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
    public function index(Request $request, Course $course)
    {
        $lessons = Lesson::where('course_id',$course->id)
        ->when($request->publish,function ($query) use ($request){ // if publish
            return $query->where('publish',$request->publish);
        })->when($request->search,function ($query) use ($request){ // if search
            return $query->where('name','Like','%'.$request->search.'%')->OrWhere('description','Like','%'.$request->search.'%');
        })->get();

        return $this->sendResponse("",['lessons' => $lessons]);
    }

    /**
     * @OA\Post(
     *     path="/api/dashboard/courses/{course_id}/lessons",
     *      tags={"Dashboard Api Lessons"},
     *     summary="Add New Lessons",
     * @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="string"),
     *             @OA\Property(property="description", type="string", example="string"),
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     * )
     */
    public function store(Request $request, Course $course)
    {
        //Validated
        $validate = Validator::make($request->all(),
        [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
        ]);


        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $request_data = $validate->validated();
        $request_data['course_id'] = $course->id;
        $lesson = Lesson::create($request_data);

        return $this->sendResponse("Lesson Created Successfully",['lesson' => $lesson]);
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/lessons/{lesson_id}",
     *      tags={"Dashboard Api Lessons"},
     *     summary="show lesson",
     *     @OA\Parameter(
     *         name="lesson_id",
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
    public function show(Lesson $lesson)
    {
        return $this->sendResponse("",['lesson' => $lesson]);
    }

    /**
     * @OA\Put(
     *     path="/api/dashboard/lessons/{lesson_id}",
     *      tags={"Dashboard Api Lessons"},
     *     summary="Updated Lesson",
     * @OA\Parameter(
     *          name="lesson_id",
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
    public function update(Request $request, Lesson $lesson)
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

        $request_data['publish'] = "unpublish";
        $lesson->update($request_data);

        return $this->sendResponse("Lesson Updated Successfully",['lesson' => $lesson]);
    }

    /**
     * @OA\Delete(
     *     path="/api/dashboard/lessons/{lesson_id}",
     *      tags={"Dashboard Api Lessons"},
     *     summary="Delete Lesson",
     *     @OA\Parameter(
     *         name="lesson_id",
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
    public function destroy(Lesson $lesson)
    {
        if($lesson->video != 'video/zNAS2X0zOi3RsC58jRqVf5gqmEodZl2DeYEsbGhr.mp4'){
            Storage::disk('public')->delete($lesson->video);
        }
        if($lesson->attached != 'attached/hasjhRZGDGT8ptnIBfyo4voFTFHvcOsnr5FRSlJA.pdf'){
            Storage::disk('public')->delete($lesson->attached);
        }
        $lesson->delete();
        return $this->sendResponse("Deleted Data Successfully");
    }

    /**
     * @OA\Post(
     *     path="/api/dashboard/lessons/{lesson_id}/upload-video",
     *      tags={"Dashboard Api Lessons"},
     *     summary="upload video Lesson",
     *     @OA\Parameter(
     *         name="lesson_id",
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
    public function uploadVideo(Request $request, Lesson $lesson)
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

        if($lesson->video != 'video/zNAS2X0zOi3RsC58jRqVf5gqmEodZl2DeYEsbGhr.mp4' || $lesson->video != null){
            Storage::disk('public')->delete($lesson->video);
        }
        $path_video = $request->file('video')->store('video',['disk' => 'public']);

        $lesson->update([
            'publish'=> "unpublish",
            'video'=> $path_video,
        ]);

        return $this->sendResponse("The video has been uploaded successfully",['lesson' => $lesson]);
    }

    /**
     * @OA\Post(
     *     path="/api/dashboard/lessons/{lesson_id}/upload-file",
     *      tags={"Dashboard Api Lessons"},
     *     summary="upload file Lesson",
     *     @OA\Parameter(
     *         name="lesson_id",
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
    public function uploadFile(Request $request, Lesson $lesson)
    {
        //Validated
        $validate = Validator::make($request->all(),
        [
            'attached' => 'required|mimes:pdf|max:8192',
        ]);

        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

            if($lesson->attached != 'attached/hasjhRZGDGT8ptnIBfyo4voFTFHvcOsnr5FRSlJA.pdf' || $lesson->attached != null){
                Storage::disk('public')->delete($lesson->attached);
            }
            $path_attached = $request->file('attached')->store('attached',['disk' => 'public']);


        $lesson->update([
            'publish'=> "unpublish",
            'attached'=> $path_attached,
        ]);
        return $this->sendResponse("The file has been uploaded successfully",['lesson' => $lesson]);
    }

    /**
     * @OA\Put(
     *     path="/api/dashboard/lessons/{lesson_id}/approve",
     *      tags={"Dashboard Api Lessons"},
     *     summary="Approve Lesson",
     *     @OA\Parameter(
     *         name="lesson_id",
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
     *             @OA\Property(property="publish", type="boolen", example="publish or unpublish"),
     *         ),
     *     ),
     *       @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=404, description="Resource Not Found")
     *    )
     */
    public function approve(Request $request, Lesson $lesson)
    {
        //Validated
        $validate = Validator::make($request->all(),
        [
            'publish' => 'required|in:publish,unpublish',
        ]);

        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $lesson->update([
            'publish'=> $request->publish,
        ]);

        return $this->sendResponse("Lesson Approved Successfully",['lesson' => $lesson]);
    }
}
