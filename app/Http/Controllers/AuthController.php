<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
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
     *             @OA\Property(property="email", type="string", example="manger@app.com"),
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
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validate->errors()
                ], 401);
            }

            if(!Auth::guard('student')->attempt($request->only(['email', 'password']))){
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                ], 401);
            }

            $student = Auth::guard('student')->user();
            return response()->json([
                'status' => true,
                'message' => 'Student Logged In Successfully',
                'token' => $student->createToken("token",['student'])->plainTextToken
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
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
        try {
            $request->user()->tokens->each(function ($token, $key) {
                $token->delete();
            });
        return response()->json([
        'message' => 'Successfully logged out'
        ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
