<?php

namespace App\Http\Controllers\Dashboard;
use App\Models\Session;
use App\Models\OfflineExam;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController as BaseController;

class OfflineExamController extends BaseController
{
    public function __construct()
    {
        $this->middleware(['permission:sessions_read'])->only('index');
        $this->middleware(['permission:sessions_create'])->only('store');
        $this->middleware(['permission:sessions_delete'])->only('destroy');
    }

     /**
     * @OA\Get(
     *     path="/api/dashboard/sessions/{session_id}/offline-exams",
     *      tags={"Dashboard Api Offline Exam"},
     *     summary="show lesson",
     *     @OA\Parameter(
     *         name="session_id",
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
    public function index(Request $request, Session $session)
    {
        $offlineExam = OfflineExam::with('session')->where("session_id",$session->id)->first();

        return $this->sendResponse("",['offlineExam' => $offlineExam]);
    }

    /**
     * @OA\Post(
     *     path="/api/dashboard/sessions/{session_id}/offline-exams",
     *      tags={"Dashboard Api Offline Exam"},
     *     summary="Add New Offline Exam",
     * @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="images", type="string", example="array files"),
     *             @OA\Property(property="max_grade", type="integer", example="100"),
     *
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     * )
     */
    public function store(Request $request, Session $session)
    {
         //Validated
         $validate = Validator::make($request->all(),
         [
             'images' => 'required',
             'images.*' => 'required|image|mimes:jpg,png,jpeg,gif,svg',
             'max_grade'=> 'required|integer|max:1000',
         ]);

         if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
         }
         $images = [];
        if ($request->file('images')) {
            $path = "questions";
            foreach($request->file('images') as $image){
                    $imageName = Str::random(20) . uniqid()  . '.webp';
                        Image::make($image)->encode('webp', 65)->resize(600, null, function ($constraint) {
                            $constraint->aspectRatio();
                            })->save(Storage::disk('public')->path($path.'/'.$imageName));
                            array_push($images, $path.'/'.$imageName);
                }
        }
        $request_data = $validate->validated();
        $request_data['images'] = $images;
         $session->offlineExam()->create($request_data);
         return $this->sendResponse("Offline Exam Created Successfully");
    }

    /**
     * @OA\Delete(
     *     path="/api/dashboard/offline-exams/{offlineExam_id}",
     *      tags={"Dashboard Api Offline Exam"},
     *     summary="Delete Offline Exam",
     *     @OA\Parameter(
     *         name="offlineExam_id",
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
    public function destroy(OfflineExam $offlineExam)
    {
        $offlineExam->delete();
        return $this->sendResponse("Deleted Data Successfully");
    }
}
