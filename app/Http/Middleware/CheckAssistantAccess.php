<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;

class CheckAssistantAccess
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
        $currentUser = auth()->user();
        $assistant_id = $request->route('assistant');
        $assistant = User::whereRoleIs('assistant')->where('id',$assistant_id)->first();

        if ($currentUser->hasRole('assistant')){
            $request->route()->setParameter('assistant', $currentUser);
            return $next($request);
        }

        if(!$assistant || ($currentUser->hasRole('teacher') && $assistant->user_id != $currentUser->id)){
            return response()->json([
                'success' => false,
                'status_code' => 404,
                'message' => 'The Assistant Not Fount'
              ], 200);
        }

        return $next($request);
    }
}
