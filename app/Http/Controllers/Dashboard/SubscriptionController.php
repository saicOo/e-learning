<?php

namespace App\Http\Controllers\Dashboard;

use Carbon\Carbon;
use App\Models\Course;
use App\Models\Student;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Rules\ValidSubscription;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class SubscriptionController extends Controller
{

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
        $subscriptions = Subscription::with(['student:id,email,level_id','student.level:id,name','course:id,name'])
        ->when($request->student_id,function ($query) use ($request){ // if student_id
            return $query->where('student_id',$request->student_id);
        })->when($request->course_id,function ($query) use ($request){ // if course_id
            return $query->where('course_id',$request->course_id);
        })->get();

            return response()->json([
                'status' => true,
                'data' => [
                    'subscriptions' => $subscriptions,
                ]
            ], 200);
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
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validate->errors()
            ], 401);
        }

        $current = Carbon::now();
        $end_date = $current->addMonth(1);

        $subscription = Subscription::create([
            'student_id' => $request->student_id,
            'course_id' => $request->course_id,
            'start_date' => now(),
            'end_date' => $end_date
        ]);


        return response()->json([
            'status' => true,
            'message' => "Student Is Subscription Successfully",
        ], 200);
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
        return response()->json([
            'status' => true,
            'data' => [
                'subscription' => $subscription,
            ]
        ], 200);
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
        return response()->json([
            'status' => true,
            'message' => 'Deleted Data Successfully',
        ], 200);
    }
}
