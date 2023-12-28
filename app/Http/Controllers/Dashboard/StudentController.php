<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\BaseController as BaseController;


class StudentController extends BaseController
{
    public function __construct()
    {
        $this->middleware(['permission:students_create'])->only('store');
        $this->middleware(['permission:students_update'])->only('update');
        $this->middleware(['permission:students_delete'])->only('destroy');
    }
    /**
     * @OA\Get(
     *     path="/api/dashboard/students",
     *      tags={"Dashboard Api Students"},
     *     summary="get all students",
     * @OA\Parameter(
     *         name="publish",
     *         in="query",
     *         description="filter students with publish (publish , unpublish)",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     * @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="filter search name , email or phone",
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
        $students = Student::query();

        // Filter by course name
        if ($request->has('publish')) {
            $students->where('publish', $request->input('publish'));
        }
        // Filter by course name
        if ($request->has('search')) {
            $search = $request->has('search');
            $students->where(function($query) use ($search) {
                return $query->where('name', 'LIKE', '%'.$search.'%')
                    ->orWhere('phone', 'LIKE', '%'.$search.'%')
                    ->orWhere('email', 'LIKE', '%'.$search.'%');
            });
        }


        $students = $students->get();

            return $this->sendResponse("",['students' => $students]);
    }


    /**
     * @OA\Post(
     *     path="/api/dashboard/students",
     *      tags={"Dashboard Api Students"},
     *     summary="Add New Student",
     * @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="string"),
     *             @OA\Property(property="email", type="string", example="string"),
     *             @OA\Property(property="phone", type="string", example="string"),
     *             @OA\Property(property="password", type="string", example="string"),
     *             @OA\Property(property="password_confirmation", type="string", example="string"),
     *             @OA\Property(property="attendance_type", type="enum", example="online , offline"),
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
                'email' => 'required|string|email|max:255|unique:students,email',
                'phone' => 'required|numeric|digits:11|unique:users,phone',
                'password' => 'required|string|max:255|confirmed',
                'attendance_type' => 'required|in:online,offline',
            ]);

            if($validate->fails()){
                return response()->json([
                    'success' => false,
                    'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'message' => 'validation error',
                    'errors' => $validate->errors()
                ], 200);
            }

            $student = Student::create([
                'name' => $request->name,
                'email' => $request->email,
                'attendance_type' => $request->attendance_type,
                'phone' => $request->phone,
                'password' => Hash::make($request->password)
            ]);

        return $this->sendResponse("Student Created Successfully");
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/students/{student_id}",
     *      tags={"Dashboard Api Students"},
     *     summary="show student",
     *     @OA\Parameter(
     *         name="student_id",
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
    public function show(Student $student)
    {
        foreach ($student->attempts as $attempt) {
            $attempt->quiz;
        }
        $student->courses;
        return $this->sendResponse("",['student' => $student]);
    }

    /**
     * @OA\Put(
     *     path="/api/dashboard/students/{student_id}",
     *      tags={"Dashboard Api Students"},
     *     summary="update student",
     * @OA\Parameter(
     *          name="student_id",
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
     *             @OA\Property(property="email", type="string", example="string"),
     *             @OA\Property(property="phone", type="string", example="string"),
     *             @OA\Property(property="attendance_type", type="enum", example="online , offline"),
     *             @OA\Property(property="publish", type="boolen", example="integer"),
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=404, description="Resource Not Found")
     * )
     */
    public function update(Request $request, Student $student)
    {
            //Validated
            $validate = Validator::make($request->all(),
            [
                'name' => 'nullable|string|max:255',
                'email' => 'nullable|string|email|max:255|unique:students,email,'.$student->id,
                'phone' => 'nullable|numeric|digits:11|unique:students,phone,'.$student->id,
                'attendance_type' => 'nullable|in:online,offline',
            ]);

            if($validate->fails()){
                return response()->json([
                    'success' => false,
                    'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'message' => 'validation error',
                    'errors' => $validate->errors()
                ], 200);
            }

            $student->update($validate->validated());

            return $this->sendResponse("Student Updated Successfully",['student' => $student]);

    }

    /**
     * @OA\Put(
     *     path="/api/dashboard/students/{student_id}/change-password",
     *      tags={"Dashboard Api Students"},
     *     summary="change password student",
     * @OA\Parameter(
     *          name="student_id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     * @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="old_password", type="string", example="string"),
     *             @OA\Property(property="new_password", type="string", example="string"),
     *             @OA\Property(property="new_password_confirmation", type="string", example="string"),
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=404, description="Resource Not Found")
     * )
     */
    public function changePassword(Request $request, Student $student)
    {
            //Validated
        $validate = Validator::make($request->all(),
        [
            'old_password' => 'required',
            'new_password' => 'required|confirmed',
        ]);


        #Match The Old Password
        if(!Hash::check($request->old_password, $student->password)){
            $validate->after(function($validate) {
                $validate->errors()->add('old_password', "Old Password Doesn't match!");
              });
        }

        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        #Update the new Password
        $student->update([
            'password' => Hash::make($request->new_password)
        ]);

        return $this->sendResponse("Password changed successfully!",['student' => $student]);
    }

    /**
     * @OA\Delete(
     *     path="/api/dashboard/students/{student_id}",
     *      tags={"Dashboard Api Students"},
     *     summary="Delete Student",
     *     @OA\Parameter(
     *         name="student_id",
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
    public function destroy(Student $student)
    {
        if($student->image != 'students/default.webp' ||  $student->image){
            Storage::disk('public')->delete($student->image);
        }
        $student->delete();

        return $this->sendResponse("Deleted Data Successfully");
    }

    /**
     * @OA\Put(
     *     path="/api/dashboard/students/{student_id}/approve",
     *      tags={"Dashboard Api Students"},
     *     summary="Approve Students",
     *     @OA\Parameter(
     *         name="student_id",
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
    public function approve(Request $request, Student $student)
    {
        //Validated
        $validate = Validator::make($request->all(),
        [
            'publish' => 'required|in:publish,unpublish',
        ]);

        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $student->update([
            'publish'=> $request->publish,
        ]);

        return $this->sendResponse("Student ".$request->publish." successfully");
    }
}
