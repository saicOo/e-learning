<?php

namespace App\Http\Controllers\Dashboard;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseController
{

    /**
     * @OA\Post(
     *     path="/api/dashboard/login",
     *      operationId="authLogin",
     *      tags={"Dashboard Api Auth User"},
     *     summary="Login User in Dashboard",
     * @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *              required={"email", "password"},
     *             @OA\Property(property="email", type="email", example="manager@app.com"),
     *             @OA\Property(property="password", type="string", example="1234"),
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     * )
     */
    public function login(Request $request){

            $validateUser = Validator::make($request->all(),
            [
                'email' => 'required|email|string|max:255',
                'password' => 'required'
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'success' => false,
                    'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 200);
            }

            $user = User::with('roles:id,name,display_name','permissions:id,name,display_name')->where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'status_code' => Response::HTTP_UNAUTHORIZED,
                    'message' => 'Email or password is incorrect!'
                ], 200);
            }

            $token = $user->createToken('token',['user'])->plainTextToken;
            $expiry_minutes = 60 * 24;// 1 day
            // $cookie = cookie('token', $token, $expiry_minutes); // 1 minute
            $cookie = cookie('token', $token, $expiry_minutes)->withSameSite('None'); // 1 minute
            $expiry_date = Carbon::now();
            $expiry_date = $expiry_date->addMinutes($expiry_minutes);
             return response()->json([
                'success' => true,
                'message' => 'User Logged In Successfully',
                'data' => [
                    'user' => $user,
                    'expiry_token' => $expiry_date]
                ],200)->withCookie($cookie);
    }

    /**
     * @OA\Post(
     *     path="/api/dashboard/logout",
     *     tags={"Dashboard Api Auth User"},
     *     summary="Auth Logout",
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        $cookie = cookie()->forget('token');

        return response()->json([
            'message' => 'Logged out successfully!'
        ])->withCookie($cookie);
    }
}
