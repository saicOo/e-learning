<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Level;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController as BaseController;

class LevelController extends BaseController
{

    /**
     * @OA\Get(
     *     path="/api/dashboard/levels",
     *      tags={"Dashboard Api Levels"},
     *     summary="Get All Levels",
     *       @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     *    )
     */
    public function index()
    {
        $levels = Level::all();
        return $this->sendResponse("",['levels' => $levels]);
    }

}
