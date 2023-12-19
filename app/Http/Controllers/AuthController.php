<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
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

           try {
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

            $token = $student->createToken('token_student',['student'])->plainTextToken;

            $expiry_minutes = 10;
            // $cookie = cookie('token', $token, 60 * 24); // 1 day
            $cookie = cookie('token', $token, $expiry_minutes); // 1 minute
            // $cookie = cookie('token_student', $token, $expiry_minutes)->withSameSite('None'); // 1 minute
            $expiry_date = Carbon::now();
            $expiry_date = $expiry_date->addMinutes($expiry_minutes);
            return response()->json([
                'status' => true,
                'message' => 'Student Logged In Successfully',
                'data' => [
                    'student' => $student,
                    'expiry_token' => $expiry_date,
                ]
                ],200)->withCookie($cookie);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
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
     *      tags={"Front Api Profile Student"},
     *     summary="Show Data Student",
     *       @OA\Response(response=200, description="OK"),
     *    )
     */
    public function profile(Request $request)
    {
            $student = $request->user();
            return response()->json([
                'status' => true,
                'data' => $student,
            ], 200);
    }
}
