<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class QuizRepetition
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
        $attempt = $request->attributes->get('attempt');

        if($attempt && $attempt->status_passed == "repetition"){
            $currentTime = now();
            $endTime = $attempt->created_at->addMinutes(5);
            if($currentTime >= $endTime){
                $attempt->delete();
            }else{
                $remainingTime = $endTime->diffInMinutes($currentTime);
                return response()->json([
                    'status_code' => 403,
                    'success' => false,
                    'message' => 'There are '.$remainingTime.' minutes left to retake the quiz'
                  ], 200);
            }
        }
        return $next($request);
    }
}
