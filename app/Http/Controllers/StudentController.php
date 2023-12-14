<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/profile",
     *      tags={"Front Api Profile Student"},
     *     summary="Show Data Student",
     *       @OA\Response(response=200, description="OK"),
     *    )
     */
    public function profile(Request $request)
    {
            $student = $request->user();
            return response()->json([
                'status' => true,
                'data' => $student,
            ], 200);
    }

}
