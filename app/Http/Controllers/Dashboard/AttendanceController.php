<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\BaseController as BaseController;

class AttendanceController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/dashboard/courses/{course_id}/attendances",
     *      tags={"Dashboard Api Attendances"},
     *     summary="get all attendances",
     *   @OA\Parameter(
     *         name="course_id",
     *         in="path",
     *         description="filter attendances with course",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     * @OA\Parameter(
     *         name="publish",
     *         in="query",
     *         description="filter attendances with publish (publish , unpublish)",
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
        $attendances = Attendance::query();

        $attendances->with(["student","course"]);

        $attendances->where('course_id', $course->id);

        $attendances = $attendances->get();

        return $this->sendResponse("",['attendances' => $attendances]);
    }

    /**
     * @OA\Post(
     *     path="/api/dashboard/courses/{course_id}/attendances",
     *      tags={"Dashboard Api Attendances"},
     *     summary="Add New Attendances",
     * @OA\Parameter(
     *          name="course_id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     * @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="date", type="string", example="string"),
     *             @OA\Property(property="status", type="string", example="string"),
     *             @OA\Property(property="student_id", type="string", example="string"),
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
            'date' => 'required|date',
            'status' => 'required|in:present,absent',
            'student_id' => 'required|exists:students,id',
        ]);


        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $request_data = $validate->validated();
        $request_data['course_id'] = $course->id;
        $attendance = Attendance::create($request_data);

        return $this->sendResponse("attendance Created Successfully",['attendance' => $attendance]);
    }

    /**
     * @OA\Put(
     *     path="/api/dashboard/attendances/{attendance_id}",
     *      tags={"Dashboard Api Attendances"},
     *     summary="Updated attendance",
     * @OA\Parameter(
     *          name="attendance_id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     * @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="date", type="string", example="string"),
     *             @OA\Property(property="status", type="string", example="string"),
     *             @OA\Property(property="student_id", type="string", example="string"),
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=404, description="Resource Not Found")
     * )
     */
    public function update(Request $request, attendance $attendance)
    {
        //Validated
        $validate = Validator::make($request->all(),
        [
            'date' => 'required|date',
            'status' => 'required|in:present,absent',
            'student_id' => 'required|exists:students,id',
        ]);

        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $request_data = $validate->validated();

        $request_data['publish'] = "unpublish";
        $attendance->update($request_data);

        return $this->sendResponse("Attendance Updated Successfully",['attendance' => $attendance]);
    }

    /**
     * @OA\Delete(
     *     path="/api/dashboard/attendances/{attendance_id}",
     *      tags={"Dashboard Api Attendances"},
     *     summary="Delete attendance",
     *     @OA\Parameter(
     *         name="attendance_id",
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
    public function destroy(attendance $attendance)
    {
        $attendance->delete();
        return $this->sendResponse("Deleted Data Successfully");
    }
}
