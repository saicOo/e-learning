<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Level;
use Illuminate\Http\Request;

class LevelController extends Controller
{
    public function index()
    {
        $levels = Level::all();
            return response()->json([
                'status' => true,
                'data' => [
                    'levels' => $levels,
                ]
            ], 200);
    }

}
