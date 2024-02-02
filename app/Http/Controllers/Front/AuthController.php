<?php

namespace App\Http\Controllers\Front;

use Carbon\Carbon;
use App\Models\Student;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\UploadService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\BaseController as BaseController;

class AuthController extends BaseController
{
    protected $uploadService;

    public function __construct(UploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }
    /**
     * @OA\Post(
     *     path="/api/login",
     *      tags={"Front Api Auth Student"},
     *     summary="Login Student in Front",
     * @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="email", type="string", example="1st@app.com"),
     *             @OA\Property(property="password", type="string", example="1234"),
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     * )
     */
    public function login(Request $request){

            $validate = Validator::make($request->all(),
            [
                'email' => 'required|email',
                'password' => 'required'
            ]);

            if($validate->fails()){
                return response()->json([
                    'success' => false,
                    'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'message' => 'validation error',
                    'errors' => $validate->errors()
                ], 200);
            }
            $student = Student::where('email', $request->email)->first();

            if (!$student || !Hash::check($request->password, $student->password)) {
                return response()->json([
                    'success' => false,
                    'status_code' => Response::HTTP_UNAUTHORIZED,
                    'message' => 'Email or password is incorrect!'
                ], 200);
            }

            $token = $student->createToken('token',['student'])->plainTextToken;

            $expiry_minutes = 365 * 24; // // 1 year
            // $cookie = cookie('token', $token, $expiry_minutes);
            $cookie = cookie('token', $token, $expiry_minutes)->withSameSite('None');
            $expiry_date = Carbon::now();
            $expiry_date = $expiry_date->addMinutes($expiry_minutes);
        return response()->json([
                'success' => true,
                'message' => 'Student Logged In Successfully',
                'data' => [
                    'student' => $student,
                    'expiry_token' => $expiry_date,
                ]
                ],200)->withCookie($cookie);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Front Api Auth Student"},
     *     summary="Auth Logout",
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        $cookie = cookie()->forget('token_student');

        return response()->json([
            'message' => 'Logged out successfully!'
        ])->withCookie($cookie);


    }

    /**
     * @OA\Get(
     *     path="/api/profile",
     *      tags={"Front Api Auth Student"},
     *     summary="Show Data Student",
     *       @OA\Response(response=200, description="OK"),
     *    )
     */
    public function profile(Request $request)
    {
            $student = $request->user();
            foreach ($student->courses as $course) {
                $course->user;
            }
            return $this->sendResponse("",['student' => $student]);
    }

    /**
     * @OA\Post(
     *     path="/api/student/upload-image",
     *      tags={"Front Api Auth Student"},
     *     summary="upload image Student",
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
    public function uploadImage(Request $request)
    {
        //Validated
        $validate = Validator::make($request->all(),
        [
            'image' => 'required|image|mimes:jpg,png,jpeg,gif,svg',
        ]);

        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $student = $request->user();
        $request_data = $validate->validate();

        $image = $this->uploadService->uploadImage('students', $request->image, $student->image);

        $student->update([
            "image"=> $image,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'The Image has been uploaded successfully',
        ], 200);
    }
}
