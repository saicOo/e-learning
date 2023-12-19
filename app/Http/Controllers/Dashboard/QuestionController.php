<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Course;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class QuestionController extends Controller
{

    public function index(Request $request)
    {
        $questions = Question::when($request->course_id,function ($query) use ($request){ // if course_id
            return $query->where('course_id',$request->course_id);
        })->when($request->listen_id,function ($query) use ($request){ // if listen_id
            return $query->where('listen_id',$request->listen_id);
        })->when($request->search,function ($query) use ($request){ // if search
            return $query->where('title','Like','%'.$request->search.'%');
        })->get();

        return response()->json([
            'status' => true,
            'data' => [
                'questions' => $questions,
            ]
        ], 200);
    }

    public function store(Request $request, Course $course)
    {
        //Validated
        $validate = Validator::make($request->all(),
        [
            'title' => 'required|string|max:1000',
            'options' => 'required|array|min:1',
            'options.*' => 'required|string|max:1000',
            'grade' => 'required|integer|max:100',
            'correct_option' => 'required|in:1,2,3,4',
            'type' => 'required|in:1,2,3,4',
            'listen_id' => 'required|exists:listens,id',
        ]);


        if($validate->fails()){
            return response()->json([
                'success' => false,
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'validation error',
                'errors' => $validate->errors()
            ], 200);
        }

        $question = $course->questions()->create([
            "title" => $request->title,
            "grade" => $request->grade,
            "type" => $request->type,
            "correct_option" => $request->correct_option,
            "options" => $request->options,
            "listen_id" => $request->listen_id,
        ]);


            return response()->json([
                'status' => true,
                'message' => 'Question Created Successfully',
            ], 200);
    }

    public function destroy(Question $question)
    {
        $question->delete();
            return response()->json([
                'status' => true,
                'message' => 'Deleted Data Successfully',
            ], 200);
    }
}
