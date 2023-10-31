<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Listen;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ListenController extends Controller
{

    public function index(Request $request)
    {
        $listens = Listen::when($request->course_id,function ($query) use ($request){ // if course_id
            return $query->where('course_id',$request->course_id);
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

    public function show(Listen $listen)
    {
        return response()->json([
            'status' => true,
            'data' => [
                'listen' => $listen,
            ]
        ], 200);
    }

    public function update(Request $request, Listen $listen)
    {
        //Validated
        $validate = Validator::make($request->all(),
        [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'video' => 'required|string|max:255',
            'course_id'=> 'required|exists:courses,id',
            'active' => 'required|in:1,0',
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
            'active'=>$request->active,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Listen Updated Successfully',
        ], 200);
    }

    public function destroy(Listen $listen)
    {
        $listen->delete();
            return response()->json([
                'status' => true,
                'message' => 'Deleted Data Successfully',
            ], 200);
    }
}
