<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/teachers",
     *      tags={"Front Api Teachers"},
     *     summary="get all teachers",
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
        $teachers = User::query();
        $teachers->whereRoleIs('teacher');

        $teachers->where('publish', "publish");

        // Filter by course name
        if ($request->has('search')) {
            $search = $request->input('search');
            $teachers->where(function($query) use ($search) {
                return $query->where('name', 'LIKE', '%'.$search.'%')
                    ->orWhere('phone', 'LIKE', '%'.$search.'%')
                    ->orWhere('email', 'LIKE', '%'.$search.'%');
            });
        }


        $teachers = $teachers->withCount(['courses'])->get();

        return $this->sendResponse("",['teachers' => $teachers]);
    }

    /**
     * @OA\Get(
     *     path="/api/teachers/{teacher_id}",
     *      tags={"Front Api Teachers"},
     *     summary="show teacher",
     *     @OA\Parameter(
     *         name="teacher_id",
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
    public function show($teacher_id)
    {
        $teacher = User::whereRoleIs('teacher')->where('id',$teacher_id)->first();
        if(!$teacher || $teacher->publish != "publish"){
            return $this->sendError('The Teacher Not Fount');
        }
        $teacher->courses;
        return $this->sendResponse("",['teacher' => $teacher]);
    }
}
