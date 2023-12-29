<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Subscription;
use Illuminate\Http\Request;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user_id = $request->user()->id;
        if($request->route('lesson')){
            $course_id = $request->route('lesson')->course_id;
        }
        if($request->route('quiz')){
            $course_id = $request->route('quiz')->course_id;
        }

        $checkSubscription = Subscription::where('end_date', '>', now())->where('student_id', $user_id)->where('course_id', $course_id)->first();

        if(!$checkSubscription){
            return response()->json([
                'status_code' => 403,
                'success' => false,
                'message' => 'You are not a subscriber !'
              ], 200);
        }

        return $next($request);
    }
}
