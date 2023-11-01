<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{

    public function index()
    {
            $students = Student::with('courses')->when($request->level_id,function ($query) use ($request){ // if level_id
                return $query->where('level_id',$request->level_id);
            })->when($request->search,function ($query) use ($request){ // if search
                return $query->where('name','Like','%'.$request->search.'%')
                ->OrWhere('email','Like','%'.$request->search.'%')
                ->OrWhere('phone','Like','%'.$request->search.'%');
            })->get();
            return response()->json([
                'status' => true,
                'data' => ['studenets' => $students],
            ], 200);
    }


    public function store(Request $request)
    {
            //Validated
            $validate = Validator::make($request->all(),
            [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:students,email',
                'phone' => 'required|string|max:255|unique:users,phone',
                'password' => 'required|string|max:255|confirmed',
                'attendance_type' => 'required|in:online,offnline,mix',
                'level_id' => 'required|exists:level,id',
            ]);

            if($validate->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validate->errors()
                ], 401);
            }

            $student = Student::create([
                'name' => $request->name,
                'email' => $request->email,
                'attendance_type' => $request->attendance_type,
                'phone' => $request->phone,
                'level_id' => $request->level_id,
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Student Created Successfully',
            ], 200);

    }

    public function show(Student $student)
    {
            return response()->json([
                'status' => true,
                'data' => ['student' => $student],
            ], 200);
    }

    public function update(Request $request, Student $student)
    {
            //Validated
            $validate = Validator::make($request->all(),
            [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:students,email,'.$student->id,
                'phone' => 'required|string|max:255|unique:students,phone,'.$student->id,
                'level_id' => 'required|exists:level,id',
                'active' => 'required|in:1,0',
            ]);

            if($validate->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validate->errors()
                ], 401);
            }

            $student->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'level_id' => $request->level_id,
                'attendance_type' => $request->attendance_type,
                'active' => $request->active,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Student Updated Successfully',
            ], 200);

            // return response()->json([
            //     'status' => false,
            //     'message' => $th->getMessage()
            // ], 500);

    }

    public function destroy(Student $student)
    {
            $student->delete();
            return response()->json([
                'status' => true,
                'message' => 'Deleted Data Successfully',
            ], 200);
    }
}
