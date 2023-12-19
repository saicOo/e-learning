<?php

namespace App\Http\Controllers;

use App\Models\Listen;
use Illuminate\Http\Request;

class ListenController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/listens",
     *      tags={"Front Api Listens"},
     *     summary="get all listens",
     *   @OA\Parameter(
     *         name="course_id",
     *         in="query",
     *         description="filter listens with course",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     * @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="filter search name or description",
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
        $listens = Listen::where('active',1)->when($request->course_id,function ($query) use ($request){ // if course_id
            return $query->where('course_id',$request->course_id);
        })->when($request->search,function ($query) use ($request){ // if search
            return $query->where('name','Like','%'.$request->search.'%')->OrWhere('description','Like','%'.$request->search.'%');
        })->get();

        return response()->json([
            'status' => true,
            'data' => [
                'listens' => $listens,
            ]
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/listens/{listen_id}",
     *      tags={"Front Api Listens"},
     *     summary="show listen",
     *     @OA\Parameter(
     *         name="listen_id",
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
    public function show(Listen $listen)
    {
        if ($listen->active != 1) {
            return response()->json(
                [
                    'status_code'=>404,
                    'success' => false,
                    'message' => 'Record not found.'
                ], 200);
        }
        return response()->json([
            'status' => true,
            'data' => [
                'listen' => $listen,
            ]
        ], 200);
    }
}