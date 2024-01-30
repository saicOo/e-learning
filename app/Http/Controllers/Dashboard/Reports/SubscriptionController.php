<?php

namespace App\Http\Controllers\Dashboard\Reports;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController as BaseController;

class SubscriptionController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/dashboard/report/subscriptions",
     *      tags={"Dashboard Api Report Subscription"},
     *     summary="get monthly total subscriptions",
     * @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="filter subscriptions with user",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     * )
     */
    public function subscriptionReport(Request $request)
    {

        $user = $request->user();
        $monthlyTotalSubscriptions = Subscription::query();

        $monthlyTotalSubscriptions->select(
            'courses.name as course_name',
            DB::raw('MONTH(subscriptions.created_at) as month'),
            DB::raw('YEAR(subscriptions.created_at) as year'),
            DB::raw('SUM(subscriptions.price) as total_price')
        );

        $monthlyTotalSubscriptions->join('students', 'subscriptions.student_id', '=', 'students.id')
        ->join('courses', 'subscriptions.course_id', '=', 'courses.id');


        if ($user->hasRole('manager') && $request->has('course_id')) {
            $subscriptions->where('course_id', $request->input('course_id'));
        }else {
            if($user->hasRole('teacher')){
                $subscriptions->whereIn('course_id', $user->courses->pluck("id"));
            }
            if($user->hasRole('assistant')){
                $subscriptions->whereIn('course_id', $user->teacher->courses->pluck("id"));
            }
        }

        $monthlyTotalSubscriptions->groupBy('course_name', 'month', 'year');

        $monthlyTotalSubscriptions = $monthlyTotalSubscriptions->get();

        return $this->sendResponse("",['monthlyTotalSubscriptions' => $monthlyTotalSubscriptions]);
    }
}
