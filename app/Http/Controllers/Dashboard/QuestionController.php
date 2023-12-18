<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Course;
use App\Models\Question;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class QuestionController extends Controller
{

    public function index()
    {
        //
    }

    public function store(Request $request, Course $course)
    {
        //Validated
        $validate = Validator::make($request->all(),
        [
            'title' => 'required|string|max:1000',
            'name' => 'required|string|max:500',
            'option' => 'required|array|min:1',
            'option.*' => 'required|string|max:1000',
            'marks' => 'required|integer|max:100',
            'type' => 'required|in:1,2,3',
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
            "marks" => $request->marks,
            "type" => $request->type,
            "answer_option" => $request->answer_option,
            "listen_id" => $request->listen_id,
        ]);


            foreach ($request->options as $index => $name) {
                $question->options()->create([
                    'name' => $name,
                    'option_num' => $index + 1,
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Listen Created Successfully',
                'data' => [
                    'listen' => $listen,
                ]
            ], 200);
    }

    public function show(Question $question)
    {
        //
    }

    public function update(Request $request, Question $question)
    {
        //
    }

    public function destroy(Question $question)
    {
        //
    }
}
