<?php

namespace App\Http\Controllers\Dashboard;

use Carbon\Carbon;
use App\Models\Course;
use App\Models\Student;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Rules\ValidSubscription;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Support\Facades\Validator;

class SubscriptionController extends BaseController
{

    public function __construct()
    {
        $this->middleware(['permission:subscriptions_create'])->only('store');
        $this->middleware(['permission:subscriptions_delete'])->only('destroy');
    }
    /**
     * @OA\Get(
     *     path="/api/dashboard/subscriptions",
     *      tags={"Dashboard Api Subscriptions"},
     *     summary="get all subscriptions",
     *     @OA\Parameter(
     *         name="student_id",
     *         in="query",
     *         description="filter subscriptions with student",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *   @OA\Parameter(
     *         name="course_id",
     *         in="query",
     *         description="filter subscriptions with course",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     * )
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $subscriptions = Subscription::query();
        $subscriptions->with(['student','course']);
        // Filter by course name
        if ($request->has('student_id')) {
            $subscriptions->where('student_id', $request->input('student_id'));
        }
        // Filter by course name
        if ($request->has('course_id')) {
            $subscriptions->where('course_id', $request->input('course_id'));
        }else {
            if($user->roles[0]->name == "teacher"){
                $subscriptions->whereIn('course_id', $user->courses->pluck("id"));
            }
            if($user->roles[0]->name == "assistant"){
                $subscriptions->whereIn('course_id', $user->teacher->courses->pluck("id"));
            }
        }

        $subscriptions = $subscriptions->get();

        return $this->sendResponse("",['subscriptions' => $subscriptions]);
    }

    /**
     * @OA\Post(
     *     path="/api/dashboard/subscriptions",
     *      tags={"Dashboard Api Subscriptions"},
     *     summary="Automatically subscribe students' to courses for a month",
     * @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="student_id", type="integer", example="1"),
     *             @OA\Property(property="course_id", type="integer", example="1"),
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
            'student_id'=> ['required','exists:students,id',new ValidSubscription($request->student_id,$request->course_id)],
            'course_id'=> 'required|exists:courses,id',
        ]);

        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $current = Carbon::now();
        $end_date = $current->addMonth(1);

        $subscription = Subscription::create([
            'student_id' => $request->student_id,
            'course_id' => $request->course_id,
            'start_date' => now(),
            'end_date' => $end_date
        ]);

        return $this->sendResponse("Student Is Subscription Successfully",['subscription' => $subscription]);
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/subscriptions/{subscription_id}",
     *      tags={"Dashboard Api Subscriptions"},
     *     summary="show subscription",
     *     @OA\Parameter(
     *         name="subscription_id",
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
    public function show(Subscription $subscription)
    {
        $subscription->student;
        $subscription->course;
        return $this->sendResponse("",['subscription' => $subscription]);
    }


    /**
     * @OA\Delete(
     *     path="/api/dashboard/subscription/{subscription_id}",
     *      tags={"Dashboard Api Subscriptions"},
     *     summary="Delete Subscription",
     *     @OA\Parameter(
     *         name="subscription_id",
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
    public function destroy(Subscription $subscription)
    {
        $subscription->delete();
        return $this->sendResponse("Deleted Data Successfully");
    }
}
