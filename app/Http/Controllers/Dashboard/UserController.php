<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\User;
use App\Traits\PermissionsUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:users_read'])->only(['index','show']);
        $this->middleware(['permission:users_create'])->only('store');
        $this->middleware(['permission:users_update'])->only('update');
        $this->middleware(['permission:users_delete'])->only('destroy');
    }
    use PermissionsUser;
    /**
     * @OA\Get(
     *     path="/api/dashboard/users",
     *      tags={"Dashboard Api Users"},
     *     summary="get all users",
     *   @OA\Parameter(
     *         name="role",
     *         in="query",
     *         description="filter users with role (manger , teacher , assistant)",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     * @OA\Parameter(
     *         name="active",
     *         in="query",
     *         description="filter users with active (active = 1 , not active = 0)",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     * @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="filter search name , email or phone",
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
        $users = User::with('roles:id,name,display_name','permissions:id,name,display_name')->when($request->role,function ($query) use ($request){ // if role
            return $query->whereRoleIs($request->role);
        })->when($request->active,function ($query) use ($request){ // if active
            return $query->where('active',$request->active);
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
     *     path="/api/dashboard/users",
     *      tags={"Dashboard Api Users"},
     *     summary="Add New User",
     * @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="string"),
     *             @OA\Property(property="email", type="string", example="string"),
     *             @OA\Property(property="phone", type="string", example="string"),
     *             @OA\Property(property="password", type="string", example="string"),
     *             @OA\Property(property="password_confirmation", type="string", example="string"),
     *             @OA\Property(property="role", type="array", @OA\Items(
     *               type="string",example="manger , teacher, assistant",
     *              ),),
     *             @OA\Property(property="permissions", type="array", @OA\Items(
     *               type="string",example="user_create",
     *              ),),
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
            'phone' => 'required|numeric|digits:11|unique:users,phone',
            'role' => 'required|exists:roles,name',
            'user_id'=> 'nullable|exists:users,id',
        ]);

        $request_data = $validate->validated();

        if($request->role == 'assistant'){

            $check_teacher = User::whereRoleIs('teacher')->where('id', $request->user_id)->first();
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
                'success' => false,
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'validation error',
                'errors' => $validate->errors()
            ], 200);
        }
        if($request->image){
            $imageName = Str::random(20) . uniqid()  . '.webp';
                Image::make($request->image)->encode('webp', 65)->resize(600, null, function ($constraint) {
                    $constraint->aspectRatio();
                    })->save( Storage::disk('public')->path('users/'.$imageName));
            $request_data['image']  = 'users/'.$imageName;
        }
        $request_data['password'] = Hash::make($request->password);
        $user = User::create($request_data);
        $user->attachRole($request->role);
        $user->syncPermissions($this->createPermissionsUser($user, $request->role));
        return response()->json([
            'status' => true,
            'message' => 'User Created Successfully',
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/users/{user_id}",
     *      tags={"Dashboard Api Users"},
     *     summary="show user",
     *     @OA\Parameter(
     *         name="user_id",
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
    public function show(User $user)
    {
        return response()->json([
            'status' => true,
            'data' => [
                'user' => $user,
                'teacher' => $user->teacher,
                'assistants' => $user->assistants,
            ]
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/dashboard/users/{user_id}",
     *      tags={"Dashboard Api Users"},
     *     summary="update user",
     * @OA\Parameter(
     *          name="user_id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     * @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="string"),
     *             @OA\Property(property="email", type="string", example="string"),
     *             @OA\Property(property="phone", type="string", example="string"),
     *             @OA\Property(property="active", type="boolen", example="integer"),
     *             @OA\Property(property="permissions", type="array", @OA\Items(
     *               type="string",example="user_create",
     *              ),),
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=404, description="Resource Not Found")
     * )
     */
    public function update(Request $request, User $user)
    {
        //Validated
        $validate = Validator::make($request->all(),
        [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users,email,'.$user->id,
            'phone' => 'nullable|numeric|digits:11|unique:users,phone,'.$user->id,
            'active' => 'nullable|in:1,0',
            'user_id'=> 'nullable|exists:users,id',
            'permissions' => 'nullable|array|min:1',
            'permissions.*' => 'nullable|exists:permissions,name',
        ]);

        if($validate->fails()){
            return response()->json([
                'success' => false,
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'validation error',
                'errors' => $validate->errors()
            ], 200);
        }
        $request_data = $validate->validated();
        if($request->image){
            if($user->image != 'users/default.webp' || $user->image){
                Storage::disk('public')->delete($user->image);
            }
            $imageName = Str::random(20) . uniqid()  . '.webp';
                Image::make($request->image)->encode('webp', 65)->resize(600, null, function ($constraint) {
                    $constraint->aspectRatio();
                    })->save( Storage::disk('public')->path('users/'.$imageName));
            $request_data['image']  = 'users/'.$imageName;
        }
        $user->update($validate->validated());

        if ($request->permissions) {
            $user->syncPermissions($request->permissions);
        }
        return response()->json([
            'status' => true,
            'message' => 'User Updated Successfully',
        ], 200);

    }

    /**
     * @OA\Put(
     *     path="/api/dashboard/users/{user_id}/change-password",
     *      tags={"Dashboard Api Users"},
     *     summary="change password user",
     * @OA\Parameter(
     *          name="user_id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     * @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="old_password", type="string", example="string"),
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
            'old_password' => 'required',
            'new_password' => 'required|confirmed',
        ]);


        #Match The Old Password
        if(!Hash::check($request->old_password, $user->password)){
            $validate->after(function($validate) {
                $validate->errors()->add('old_password', "Old Password Doesn't match!");
              });
        }

        if($validate->fails()){
            return response()->json([
                'success' => false,
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'validation error',
                'errors' => $validate->errors()
            ], 200);
        }

        #Update the new Password
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Password changed successfully!',
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/dashboard/users/{user_id}",
     *      tags={"Dashboard Api Users"},
     *     summary="Delete User",
     *     @OA\Parameter(
     *         name="user_id",
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
    public function destroy(User $user)
    {
        if($user->image != 'users/default.webp' ||  $user->image){
            Storage::disk('public')->delete($user->image);
        }
        $user->delete();
            return response()->json([
                'status' => true,
                'message' => 'Deleted Data Successfully',
            ], 200);
    }

}
