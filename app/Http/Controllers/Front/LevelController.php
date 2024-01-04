<?php

namespace App\Http\Controllers\Front;

use App\Models\Level;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController as BaseController;

class LevelController extends BaseController
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
        return $this->sendResponse("",['levels' => $levels]);
    }
}
