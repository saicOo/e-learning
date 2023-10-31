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

    // public function register(Request $request){
    //         //Validated
    //         $validate = Validator::make($request->all(),
    //         [
    //             'name' => 'required|string|max:255',
    //             'email' => 'required|string|max:255|email|unique:users,email',
    //             'password' => 'required|string|max:255|confirmed',
    //             'phone' => 'required|string|max:255',
    //             'role' => 'required|in:manger,teacher,assistant'
    //         ]);

    //         if($validate->fails()){
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'validation error',
    //                 'errors' => $validate->errors()
    //             ], 401);
    //         }
    //         $request_data = $request->except(['password','password_confirmation']);
    //         $request_data['password'] = Hash::make($request->password);
    //         $user = User::create($request_data);

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'User Created Successfully',
    //         ], 200);

    // }

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
