<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Pion\Laravel\ChunkUpload\Save\ChunkSave;
use Pion\Laravel\ChunkUpload\Storage\ChunkStorage;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use App\Http\Controllers\BaseController as BaseController;
use Pion\Laravel\ChunkUpload\Exceptions\UploadMissingFileException;

class LessonController extends BaseController
{
    public function __construct()
    {
        $this->middleware(['permission:lessons_create'])->only('store');
        $this->middleware(['permission:lessons_update'])->only(['update','uploadVideo','uploadFile']);
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
     *         in="path",
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
        $lessons = Lesson::query();
        $lessons->with('quizzes.questions');
        // Filter by course name
        if ($request->has('search')) {
            $lessons->where('name', 'like', '%' . $request->input('search') . '%');
        }
        // Filter by course name
        if ($request->has('publish')) {
            $lessons->where('publish', $request->input('publish'));
        }
        $lessons->where('course_id', $course->id);

        $lessons = $lessons->orderBy('order')->get();

        return $this->sendResponse("",['lessons' => $lessons]);
    }

    /**
     * @OA\Post(
     *     path="/api/dashboard/courses/{course_id}/lessons",
     *      tags={"Dashboard Api Lessons"},
     *     summary="Add New Lessons",
     * @OA\Parameter(
     *         name="course_id",
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
        $lastLesson = Lesson::latest("order")->first();
        $request_data = $validate->validated();
        $request_data['course_id'] = $course->id;
        $request_data['order'] = $lastLesson ? $lastLesson->order + 1 : 1;
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
        $lesson->quizzes;
        foreach ($lesson->attempts as $attempt) {
            $attempt->quiz;
            $attempt->student;
        }
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
     *             @OA\Property(property="order", type="integer", example=1),
     *
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=404, description="Resource Not Found")
     * )
     */
    public function update(Request $request, Lesson $lesson)
    {
        $course = Course::find($lesson->course_id);
        $old_order = $lesson->order;
        $last_order = $course->lessons()->latest("order")->first()->order;
        //Validated
        $validate = Validator::make($request->all(),
        [
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
            'order' => [
                'nullable',
                'integer',
                'min:1',
                'max:'. $last_order,'not_in:'.$old_order
                // Rule::unique('lessons')->ignore($lesson->id)->where(function ($query) use ($lesson) {
                //     return $query->where('course_id', $lesson->course_id);
                // }),
            ],
        ]);

        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $request_data = $validate->validated();
         // اعادة ترتيب الدروس حسب الاوردر
        $request_data['publish'] = "unpublish";
        $lesson->update($request_data);
        if($request->has('order')){
            $lesson->reorderLessons($request->input('order'),$old_order);
        }

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
        if($lesson->type == 'file' && $lesson->video != null){
            Storage::disk('public')->delete($lesson->video);
        }
        if($lesson->attached != null){
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
                'video' => 'required|mimes:mp4|max:4024000',
                'video_type' => 'required|in:file',
            ]);
        }else{
            $validate = Validator::make($request->all(),
            [
                'video' => 'required',
                'video_type' => 'required|in:url',
            ]);
        }

        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $receiver = new FileReceiver('video', $request, HandlerFactory::classFromRequest($request));
        if ($receiver->isUploaded() === false) {
            throw new UploadMissingFileException();
        }

        $save = $receiver->receive();

        if ($save->isFinished()) {
            if($lesson->type == 'file' && $lesson->video != null){
                Storage::disk('public')->delete($lesson->video);
            }
            $file = $save->getFile();
            $path_video = $file->store('video',['disk' => 'public']);
            $lesson->update([
                'publish'=> "unpublish",
                'video'=> $path_video,
            ]);
            $lesson->progress()->update([
                'status'=>'repetition',
                'is_passed'=>false
            ]);
        }

        $handler = $save->handler();

        return $this->sendResponse("The video has been uploaded successfully",["done" => $handler->getPercentageDone(),'lesson' => $lesson]);
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
            'attached' => 'required|file|max:4024000',
        ]);

        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $receiver = new FileReceiver('attached', $request, HandlerFactory::classFromRequest($request));
        if ($receiver->isUploaded() === false) {
            throw new UploadMissingFileException();
        }

        $save = $receiver->receive();

        if ($save->isFinished()) {
            if($lesson->attached != 'attached/hasjhRZGDGT8ptnIBfyo4voFTFHvcOsnr5FRSlJA.pdf' || $lesson->attached != null){
                Storage::disk('public')->delete($lesson->attached);
            }
            $file = $save->getFile();
            $path_attached = $file->store('attached',['disk' => 'public']);
            $lesson->update([
                'publish'=> "unpublish",
                'attached'=> $path_attached,
            ]);
        }

        $handler = $save->handler();

        return $this->sendResponse("The file has been uploaded successfully",["done" => $handler->getPercentageDone(),'lesson' => $lesson]);
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

        return $this->sendResponse("Lesson ".$request->publish." successfully");
    }
}
