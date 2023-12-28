<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckLessonProgress
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
        // $currentLessonId = $request->route('lesson'); // Get the current lesson ID from the route

        // $previousLesson = Lesson::find($currentLessonId - 1); // Get the previous lesson
        // if (!$previousLesson) {
        //     // Handle edge case for the first lesson or lesson not found
        //     return redirect()->back()->with('error', 'No previous lesson found.');
        // }

        // $user = Auth::user();
        // $previousLessonProgress = UserLessonProgress::where('user_id', $user->id)
        //     ->where('lesson_id', $previousLesson->id)
        //     ->first();

        // if (!$previousLessonProgress || !$previousLessonProgress->is_passed) {
        //     return redirect()->back()->with('error', 'Please complete the previous lesson test.');
        // }
        return $next($request);
    }
}
