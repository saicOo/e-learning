<?php

namespace App\Http\Controllers\Front;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuizAttempt;
use App\Models\QuizProcess;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\UploadService;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\BaseController as BaseController;


class QuizAttemptController extends BaseController
{

    protected $uploadService;

    public function __construct(UploadService $uploadService)
    {
        $this->middleware(['checkSubscription','checkSubmitQuiz'])->only('submitQuiz');
        $this->uploadService = $uploadService;
    }
    /**
     * @OA\Get(
     *     path="/api/quiz-attempts",
     *      tags={"Front Api Attempts"},
     *     summary="get all attempts of student",
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     * )
     */
    public function index(Request $request)
    {
        $attempts = $request->user()->attempts;
        return $this->sendResponse("",['attempts' => $attempts]);
    }

    /**
     * @OA\Get(
     *     path="/api/quiz-attempts/{quizAttempt_id}",
     *      tags={"Front Api Attempts"},
     *     summary="show quiz attempt and answers",
     *     @OA\Parameter(
     *         name="quizAttempt_id",
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
     * )
     */
    public function show(QuizAttempt $quizAttempt)
    {
        $attempt = $quizAttempt;
        $attempt->questions;

        return $this->sendResponse("",['attempt' => $attempt]);
    }

    /**
     * @OA\Post(
     *     path="/api/quiz-attempts/{quiz_id}",
     *      tags={"Front Api Quiz Attempt"},
     *     summary="update user",
     * @OA\Parameter(
     *          name="quiz_id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     * @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="answers", type="object",example={"10":0,"25":1}
     *            ),
     *          @OA\Property(property="images", type="array", @OA\Items(
     *               type="integer",example="source file data form",
     *              ),),
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=404, description="Resource Not Found")
     * )
     */
    public function submitQuiz(Request $request, Quiz $quiz)
    {
        if ($quiz->publish != "publish") {
            return $this->sendError('Record not found.');
        }
        //Validated
        $validate = Validator::make($request->all(),
        [
            'answers' => 'nullable|array',
            'images' => 'nullable|array',
        ]);

        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $images = [];
        if ($request->file('images')) {
            $path = "answers";
            foreach($request->file('images') as $image){
                    $imageName = Str::random(20) . uniqid()  . '.webp';
                        Image::make($image)->encode('webp', 65)->resize(600, null, function ($constraint) {
                            $constraint->aspectRatio();
                            })->save(Storage::disk('public')->path($path.'/'.$imageName));
                            array_push($images, $path.'/'.$imageName);
                }
        }

        $studentId = auth()->user()->id;
        $answers = $request->input('answers');
        $totalScore = 0;
        $maxGrade = 0;

        $attempt = $quiz->attempts()->create([
            'student_id' => $studentId,
            'images'=>$images,
        ]);


        foreach ($quiz->questions as $index => $question) {

                $answer = null;
                $grade = 0;
                $questionId = $question->id;

                if(isset($answers[$questionId])){
                    if($answers[$questionId] == $question->correct_option){
                        $grade = $question->grade;
                        $totalScore += $grade;
                    }
                    $answer = isset($question->options[$answers[$questionId]]) ? $question->options[$answers[$questionId]] : null;
                }

                $attempt->questions()->attach($questionId,[
                    'answer' =>  $answer,
                    'grade' => $grade,
                ]);
                $maxGrade += $question->grade;

        }
        // Calculate overall score based on grades
        $score = $this->calculateScore($totalScore, $maxGrade);

        $statusQuiz = "pending";
        $status = "failed";
        $questionIsArticle = $quiz->questions()->where("type", 3)->first();
        $quizProcess = QuizProcess::where('student_id', $studentId)
        ->where('quiz_id', $quiz->id)->first();
        $quizProcess->update([
            'status' => 'stoped',
        ]);
        if(!$questionIsArticle && $quizProcess){
            $status = $this->quizProcess($score, $quizProcess);
            $statusQuiz = $status;
        }

        $attempt->update([
            'score'=>$score,
            'status'=>$status,
            'is_submit'=> true,
            'is_visited'=> $status == "successful" ? true : false,
        ]);

        return $this->sendResponse("Quiz Created Successfully", ['attempt' => $attempt,'status'=> $statusQuiz]);
    }

    // Add helper methods as needed
    private function calculateScore($totalScore ,$maxGrade = 10)
    {
        // Calculate the overall score as a percentage
        $overallScore = $totalScore != 0 ? ($totalScore / $maxGrade) * 100 : 0;

        return $overallScore;
    }

    private function quizProcess($score ,QuizProcess $quizProcess)
    {
            $status = "";

            if($score >= 50){
                $status = "successful";
            }else{
                $status = "failed";
            }

            return $status;
    }
}
