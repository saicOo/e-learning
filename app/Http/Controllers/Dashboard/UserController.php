<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\UploadService;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Support\Facades\Validator;

class UserController extends BaseController
{
    protected $uploadService;
    public function __construct(UploadService $uploadService)
    {
        $this->middleware(['userAccess']);
        $this->uploadService = $uploadService;
    }

    /**
     * @OA\Put(
     *     path="/api/dashboard/users/change-password/{user_id}",
     *      tags={"Dashboard Api Users"},
     *     summary="change password user",
     * @OA\Parameter(
     *         name="user_id",
     *         in="path",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     * @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="new_password", type="string", example="string"),
     *             @OA\Property(property="new_password_confirmation", type="string", example="string"),
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=404, description="Resource Not Found")
     * )
     */
    public function changePassword(Request $request, User $user)
    {
            //Validated
        $validate = Validator::make($request->all(),
        [
            'new_password' => 'required|string|min:4|max:14|confirmed',
        ]);

        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        #Update the new Password
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully!',
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/dashboard/users/upload-image/{user_id}",
     *      tags={"Dashboard Api Users"},
     *     summary="upload file User",
     * @OA\Parameter(
     *         name="user_id",
     *         in="path",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     * @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="image", type="file", example="path image"),
     *         ),
     *     ),
     *       @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=404, description="Resource Not Found")
     *    )
     */
    public function uploadImage(Request $request, User $user)
    {
        //Validated
        $validate = Validator::make($request->all(),
        [
            'image' => 'required|image|mimes:jpg,png,jpeg,gif,svg',
        ]);

        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $request_data = $validate->validate();

        $image = $this->uploadService->uploadImage('users', $request->image, $user->image);

        $user->update([
            "image"=> $image,
        ]);

        return $this->sendResponse("The Image has been uploaded successfully");
    }

}
