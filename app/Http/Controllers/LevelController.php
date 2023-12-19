<?php

namespace App\Http\Controllers;

use App\Models\Level;
use Illuminate\Http\Request;

class LevelController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/levels",
     *      tags={"Front Api Levels"},
     *     summary="Get All Levels",
     *       @OA\Response(response=200, description="OK"),
     *    )
     */
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