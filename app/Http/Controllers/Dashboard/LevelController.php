<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Level;
use Illuminate\Http\Request;

class LevelController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/levels",
     *      tags={"Levels"},
     *     summary="Get All Levels",
     *       @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
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
