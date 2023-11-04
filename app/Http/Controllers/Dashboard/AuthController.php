<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/dashboard/login",
     *      tags={"Dashboard Api Auth User"},
     *     summary="Login User in Dashboard",
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
            $validateUser = Validator::make($request->all(),
            [
                'email' => 'required|email|string|max:255',
                'password' => 'required'
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            if(!Auth::attempt($request->only(['email', 'password']))){
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                ], 401);
            }

            $user = Auth::user();
            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'token' => $user->createToken("token",['user'])->plainTextToken
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
     *     path="/api/dashboard/logout",
     *     tags={"Dashboard Api Auth User"},
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
