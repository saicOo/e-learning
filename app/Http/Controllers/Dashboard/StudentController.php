<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;


class StudentController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/dashboard/students",
     *      tags={"Dashboard Api Students"},
     *     summary="get all students",
     *   @OA\Parameter(
     *         name="level_id",
     *         in="query",
     *         description="filter students with level",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     * @OA\Parameter(
     *         name="active",
     *         in="query",
     *         description="filter students with active (active = 1 , not active = 0)",
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
            $students = Student::with(['level:id,name'])
            ->when($request->level_id,function ($query) use ($request){ // if level_id
                return $query->where('level_id',$request->level_id);
            })->when($request->active,function ($query) use ($request){ // if active
                return $query->where('active',$request->active);
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
     *             @OA\Property(property="attendance_type", type="enum", example="online , offnline , mix"),
     *             @OA\Property(property="level_id", type="integer", example="integer"),
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
                'phone' => 'required|string|max:255|unique:users,phone',
                'password' => 'required|string|max:255|confirmed',
                'attendance_type' => 'required|in:online,offnline,mix',
                'level_id' => 'required|exists:levels,id',
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
                'level_id' => $request->level_id,
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Student Created Successfully',
            ], 200);

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
            return response()->json([
                'status' => true,
                'data' => ['student' => $student],
            ], 200);
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
     *             @OA\Property(property="attendance_type", type="enum", example="online , offnline , mix"),
     *             @OA\Property(property="level_id", type="integer", example="integer"),
     *             @OA\Property(property="active", type="boolen", example="integer"),
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
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:students,email,'.$student->id,
                'phone' => 'required|string|max:255|unique:students,phone,'.$student->id,
                'level_id' => 'required|exists:levels,id',
                'attendance_type' => 'required|in:online,offnline,mix',
                'active' => 'required|in:1,0',
            ]);

            if($validate->fails()){
                return response()->json([
                    'success' => false,
                    'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'message' => 'validation error',
                    'errors' => $validate->errors()
                ], 200);
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
            //     'success' => false,
            //     'message' => $th->getMessage()
            // ], 500);

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
            return response()->json([
                'success' => false,
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'validation error',
                'errors' => $validate->errors()
            ], 200);
        }

        #Update the new Password
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Password changed successfully!',
        ], 200);
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
            $student->delete();
            return response()->json([
                'status' => true,
                'message' => 'Deleted Data Successfully',
            ], 200);
    }
}
