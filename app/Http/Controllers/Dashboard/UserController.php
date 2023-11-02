<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/users",
     *      tags={"Users"},
     *     summary="get all users",
     *   @OA\Parameter(
     *         name="role",
     *         in="query",
     *         description="filter users with role",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             default="manger , teacher , assistant",
     *             type="integer",
     *         ),
     *     ),
     * @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="filter search name , email or phone",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             default="keyword",
     *             type="string",
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     * )
     */
    public function index(Request $request)
    {
        $users = User::when($request->role,function ($query) use ($request){ // if role
            return $query->where('role',$request->role);
        })->when($request->search,function ($query) use ($request){ // if search
            return $query->where('name','Like','%'.$request->search.'%')
            ->OrWhere('email','Like','%'.$request->search.'%')
            ->OrWhere('phone','Like','%'.$request->search.'%');
        })->get();

        return response()->json([
            'status' => true,
            'data' => [
                'users' => $users,
            ]
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/users",
     *      tags={"Users"},
     *     summary="Add New User",
     * @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="string"),
     *             @OA\Property(property="email", type="string", example="string"),
     *             @OA\Property(property="phone", type="string", example="string"),
     *             @OA\Property(property="password", type="string", example="string"),
     *             @OA\Property(property="role", type="enum", example="manger , teacher , assistant"),
     *             @OA\Property(property="user_id", type="integer", example="Sets the teacher assistant's ID"),
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     * )
     */
    public function store(Request $request)
    {
        //Validated
        $validate = Validator::make($request->all(),
        [
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|email|unique:users,email',
            'password' => 'required|string|max:255|confirmed',
            'phone' => 'required|string|max:255|unique:users,phone',
            'role' => 'required|in:manger,teacher,assistant',
            'user_id'=> 'nullable|exists:users,id'
        ]);

        $request_data = $request->only(['name','email','phone','role']);

        if($request->role == 'assistant'){

            $check_teacher = User::where('role','teacher')->where('id', $request->user_id)->first();
            if(!$check_teacher){
                $validate->after(function($validate) {
                    $validate->errors()->add('user_id', 'You have chosen the wrong reference teacher');
                  });
            }else{
                $request_data['user_id'] = $request->user_id;
            }
        }

        if($validate->fails()){
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validate->errors()
            ], 401);
        }

        $request_data['password'] = Hash::make($request->password);
        $user = User::create($request_data);

        return response()->json([
            'status' => true,
            'message' => 'User Created Successfully',
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/users/{user_id}",
     *      tags={"Users"},
     *     summary="show user",
     *     @OA\Parameter(
     *         name="user_id",
     *         in="path",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *             default="1",
     *             type="integer",
     *         ),
     *     ),
     *       @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     *    )
     */
    public function show(User $user)
    {
        return response()->json([
            'status' => true,
            'data' => [
                'user' => $user,
            ]
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/users/{user_id}",
     *      tags={"Users"},
     *     summary="update user",
     * @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="string"),
     *             @OA\Property(property="email", type="string", example="string"),
     *             @OA\Property(property="phone", type="string", example="string"),
     *             @OA\Property(property="active", type="boolen", example="integer"),
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     * )
     */
    public function update(Request $request, User $user)
    {
        //Validated
        $validate = Validator::make($request->all(),
        [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'phone' => 'required|string|max:255|unique:users,phone,'.$user->id,
            'active' => 'required|in:1,0',
        ]);

        if($validate->fails()){
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validate->errors()
            ], 401);
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'active' => $request->active,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'User Updated Successfully',
        ], 200);

    }


    /**
     * @OA\Delete(
     *     path="/api/users/{user_id}",
     *      tags={"Users"},
     *     summary="Delete User",
     *     @OA\Parameter(
     *         name="user_id",
     *         in="path",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *             default="1",
     *             type="integer",
     *         ),
     *     ),
     *       @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     *    )
     */
    public function destroy(User $user)
    {
        $user->delete();
            return response()->json([
                'status' => true,
                'message' => 'Deleted Data Successfully',
            ], 200);
    }
}
