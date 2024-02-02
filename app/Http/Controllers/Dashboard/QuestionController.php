<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\UploadService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Pion\Laravel\ChunkUpload\Save\ChunkSave;
use Pion\Laravel\ChunkUpload\Storage\ChunkStorage;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use App\Http\Controllers\BaseController as BaseController;

class QuestionController extends BaseController
{
    protected $uploadService;

    public function __construct(UploadService $uploadService)
    {
        $this->middleware(['permission:questions_create'])->only('store');
        $this->middleware(['permission:questions_delete'])->only('destroy');
        $this->uploadService = $uploadService;
    }
    /**
     * @OA\Get(
     *     path="/api/dashboard/questions",
     *      tags={"Dashboard Api Questions"},
     *     summary="get all questions",
     *   @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="filter questions with lesson",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
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
     * @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="filter of 1=>TrueFalse, 2=>Choice,3 =>Article",
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
        $questions = Question::query();
        $questions->with('category:id,name');
        // Filter by course name
        if ($request->has('search')) {
            $questions->where('title', 'like', '%' . $request->input('search') . '%');
        }
        // Filter by course name
        if ($request->has('category_id')) {
            $questions->where('category_id', $request->input('category_id'));
        }
        // Filter by course name
        if ($request->has('type')) {
            $questions->where('type', $request->input('type'));
        }

        $questions = $questions->get();

        return $this->sendResponse("",['questions' => $questions]);

    }
    /**
     * @OA\Post(
     *     path="/api/dashboard/questions",
     *      tags={"Dashboard Api Questions"},
     *     summary="Add New Question",
     * @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="title", type="string", example="string"),
     *             @OA\Property(property="image", type="string", example="path file"),
     *             @OA\Property(property="grade", type="integer", example="integer"),
     *             @OA\Property(property="correct_option", type="integer", example="index option : 0, 1, 2, 3" ),
     *             @OA\Property(property="type", type="integer", example="1=>TrueFalse, 2=>Choice,3 =>Article'"),
     *             @OA\Property(property="category_id", type="integer", example="integer"),
     *             @OA\Property(property="options", type="array", @OA\Items(
     *               type="string",example="option answer",
     *              ),),
     *
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     * )
     */
    public function store(Request $request)
    {
        $rules = [
            'title' => 'required|string|max:1000',
            'image' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg',
            'grade' => 'required|integer|max:100',
            'type' => 'required|in:1,2,3', //1=>TrueFalse, 2=>Choice,3 =>Article'
            'category_id' => 'required|exists:categories,id',
        ];

        if ($request->type != 3) {

            if($request->type == 1){
                $rules += [
                    'options' => 'required|array|min:2|max:2',
                    'correct_option' => 'required|in:0,1',
                ];
            }

            if($request->type == 2){
                $rules += [
                    'options' => 'required|array|min:3|max:4',
                    'correct_option' => 'required|in:0,1,2,3'
                ];
            }

            $rules += [
                'options.*' => 'required|string|max:1000',
            ];
        }else {
            $rules += [
                'image' => 'required|image|mimes:jpg,png,jpeg,gif,svg',
            ];
        }

        //Validated
        $validate = Validator::make($request->all(), $rules);

        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $request_data = $validate->validate();
        if($request->image && $request->type == 3){
            $request_data['image'] = $this->uploadService->uploadImage('questions', $request->image);
        }
        Question::create($request_data);

        return $this->sendResponse("Question Created Successfully");
    }

    /**
     * @OA\Delete(
     *     path="/api/dashboard/questions/{question_id}",
     *      tags={"Dashboard Api Questions"},
     *     summary="Delete Question",
     *     @OA\Parameter(
     *         name="question_id",
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
    public function destroy(Question $question)
    {
        if($question->image != 'questions/default.webp' ||  $question->image){
            Storage::disk('public')->delete($question->image);
        }
        $question->delete();
        return $this->sendResponse("Deleted Data Successfully");
    }

    /**
     * @OA\Post(
     *     path="/api/dashboard/questions/{question_id}/upload-video",
     *      tags={"Dashboard Api Questions"},
     *     summary="upload video question",
     *     @OA\Parameter(
     *         name="question_id",
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
    public function uploadVideo(Request $request, Question $question)
    {
        $validate = Validator::make($request->all(),
        [
            'video' => 'required|mimes:mp4|max:4024000',
        ]);

        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $receiver = new FileReceiver('video', $request, HandlerFactory::classFromRequest($request));
        if ($receiver->isUploaded() === false) {
            throw new UploadMissingFileException();
        }

        $save = $receiver->receive();

        if ($save->isFinished()) {
            if($question->video != null){
                Storage::disk('public')->delete($question->video);
            }
            $file = $save->getFile();
            $path_video = $file->store('video',['disk' => 'public']);
            $question->update([
                'video'=> $path_video,
            ]);
        }

        $handler = $save->handler();

        return $this->sendResponse("The video has been uploaded successfully",["done" => $handler->getPercentageDone(),'question' => $question]);
    }
}
