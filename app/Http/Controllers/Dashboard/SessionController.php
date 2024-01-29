<?php

namespace App\Http\Controllers\Dashboard;

use Carbon\Carbon;
use App\Models\Course;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\BaseController as BaseController;

class SessionController extends BaseController
{
    public function __construct()
    {
        $this->middleware(['permission:sessions_read'])->only(['store','show']);
        $this->middleware(['permission:sessions_create'])->only('store');
        $this->middleware(['permission:sessions_update'])->only('update');
        $this->middleware(['permission:sessions_delete'])->only('destroy');

        $this->middleware(['checkApiAffiliation','checkCourseOffline'])->only(['index','store']);

    }
    /**
     * @OA\Get(
     *     path="/api/dashboard/courses/{course_id}/sessions",
     *      tags={"Dashboard Api Sessions"},
     *     summary="get all sessions",
     *   @OA\Parameter(
     *         name="course_id",
     *         in="path",
     *         description="filter sessions with course",
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
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     * )
     */
    public function index(Request $request, Course $course)
    {
        $sessions = Session::query();

        $sessions->with(["course","students"]);

        $sessions->where('course_id', $course->id);

        if ($request->has('session_date')) {
            $lessons->date('session_date', $request->input('session_date'));
        }

        $sessions = $sessions->get();

        return $this->sendResponse("",['sessions' => $sessions]);
    }

    /**
     * @OA\Post(
     *     path="/api/dashboard/courses/{course_id}/sessions",
     *      tags={"Dashboard Api Sessions"},
     *     summary="Add New Sessions",
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
     *             @OA\Property(property="session_date", type="string", example="2020/10/8"),
     *             @OA\Property(property="details", type="string", example="string"),
     *             @OA\Property(property="students", type="array", @OA\Items(
     *               type="integer",example="1",
     *              ),),
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     * )
     */
    public function store(Request $request, Course $course)
    {
        //Validated |unique:sessions,session_date
        $validate = Validator::make($request->all(),
        [
            'session_date' => 'required|date',
            'details' => 'nullable|string|max:255',
            'students' => 'required|array|min:1',
        ]);

        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $request_data = $validate->validated();
        // $request_data["session_date"] = Carbon::createFromFormat('Y/m/d', $request_data["session_date"]);
        $session = Session::create([
            "session_date" => $request_data["session_date"],
            "details" => isset($request_data["details"]) ? $request_data["details"] : "",
            "course_id" => $course->id,
        ]);
        foreach ($course->students as $student) {
            $status = "absent";
            $student_id = $student->id;
            if(in_array($student_id,$request_data["students"])){
                $status = "present";
            }
            $session->students()->sync([$student_id => ['status' => $status]],false);
        }

        return $this->sendResponse("Session Created Successfully",['session' => $session]);
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/sessions/{session_id}",
     *      tags={"Dashboard Api Sessions"},
     *     summary="Show Session",
     *     @OA\Parameter(
     *         name="session_id",
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
    public function show(Session $session)
    {
        $session->students;
        return $this->sendResponse("",['session' => $session]);
    }

    /**
     * @OA\Put(
     *     path="/api/dashboard/sessions/{session_id}",
     *      tags={"Dashboard Api Sessions"},
     *     summary="Updated Session",
     * @OA\Parameter(
     *          name="session_id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     * @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="session_date", type="string", example="2020/10/8"),
     *             @OA\Property(property="details", type="string", example="string"),
     *             @OA\Property(property="students", type="array", @OA\Items(
     *               type="integer",example="1",
     *              ),),
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=404, description="Resource Not Found")
     * )
     */
    public function update(Request $request, Session $session)
    {
        //Validated
        $validate = Validator::make($request->all(),
        [
            // 'session_date' => 'required|date|unique:sessions,session_date,' . $session->id,
            'session_date' => 'required|date',
            'details' => 'nullable|string|max:255',
            'students' => 'required|array|min:1',
        ]);


        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $request_data = $validate->validated();
        // $request_data["session_date"] = Carbon::createFromFormat('Y/m/d', $request_data["session_date"]);
        $session->update([
            "session_date" => $request_data["session_date"],
            "details" => isset($request_data["details"]) ? $request_data["details"] : $session->details,
        ]);

        foreach ($session->course->students as $student) {
            $status = "absent";
            $student_id = $student->id;
            if(in_array($student_id,$request_data["students"])){
                $status = "present";
            }
            $session->students()->sync([$student_id => ['status' => $status]],false);
            // $session->students()->updateExistingPivot($student_id , ['status' => $status]);
        }

        return $this->sendResponse("Session Updated Successfully",['session' => $session]);
    }

    /**
     * @OA\Delete(
     *     path="/api/dashboard/sessions/{session_id}",
     *      tags={"Dashboard Api Sessions"},
     *     summary="Delete session",
     *     @OA\Parameter(
     *         name="session_id",
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
    public function destroy(Session $session)
    {
        $session->delete();
        return $this->sendResponse("Deleted Data Successfully");
    }
}
