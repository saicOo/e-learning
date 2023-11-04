<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{

    public function profile()
    {
            $student = Auth::user();
            return response()->json([
                'status' => true,
                'data' => $student,
            ], 200);
    }

}
