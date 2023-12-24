<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Http\Response;
use App\Services\UploadService;
use App\Traits\PermissionsUser;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\BaseController as BaseController;

class TeacherController extends BaseController
{
    protected $userService;
    protected $uploadService;
    public function __construct(UserService $userService,UploadService $uploadService)
    {
        // $this->middleware(['permission:teachers_read'])->only(['index','show']);
        // $this->middleware(['permission:teachers_create'])->only('store');
        // $this->middleware(['permission:teachers_update'])->only('update');
        // $this->middleware(['permission:teachers_delete'])->only('destroy');
        $this->userService = $userService;
        $this->uploadService = $uploadService;
    }
    use PermissionsUser;
    /**
     * @OA\Get(
     *     path="/api/dashboard/teachers",
     *      tags={"Dashboard Api Teachers"},
     *     summary="get all teachers",
     * @OA\Parameter(
     *         name="active",
     *         in="query",
     *         description="filter teachers with active (active = 1 , not active = 0)",
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
        $teachers = User::when($request->active,function ($query) use ($request){ // if active
            return $query->where('active',$request->active);
        })->when($request->search,function ($query) use ($request){ // if search
            return $query->where('name','Like','%'.$request->search.'%')
            ->OrWhere('email','Like','%'.$request->search.'%')
            ->OrWhere('phone','Like','%'.$request->search.'%');
        })->whereRoleIs('teacher')->get();

        return $this->sendResponse("",['teachers' => $teachers]);
    }

    /**
     * @OA\Post(
     *     path="/api/dashboard/teachers",
     *      tags={"Dashboard Api Teachers"},
     *     summary="Add New Teacher",
     * @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="string"),
     *             @OA\Property(property="email", type="string", example="string"),
     *             @OA\Property(property="phone", type="string", example="string"),
     *             @OA\Property(property="password", type="string", example="string"),
     *             @OA\Property(property="password_confirmation", type="string", example="string"),
     *             @OA\Property(property="image", type="file", example="path image"),
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
            'image' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg',
            'phone' => 'required|numeric|digits:11|unique:users,phone',
        ]);

        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $request_data = $validate->validated();
        if($request->image){
            $request_data['image'] = $this->uploadService->uploadImage('users', $request->image);
        }
        $teacher = $this->userService->createUser($request_data);
        $teacher->attachRole('teacher');
        $teacher->syncPermissions($this->createPermissionsUser($teacher, 'teacher'));
        return $this->sendResponse('Teacher Created Successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/teachers/{teacher_id}",
     *      tags={"Dashboard Api Teachers"},
     *     summary="show teacher",
     *     @OA\Parameter(
     *         name="teacher_id",
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
    public function show($id)
    {
        $teacher = User::whereRoleIs('teacher')->where('id',$id)->first();
        if(!$teacher){
            return $this->sendError('The Teacher Not Fount');
        }
        $teacher->assistants;
        return $this->sendResponse("",['teacher' => $teacher]);
    }

    /**
     * @OA\Put(
     *     path="/api/dashboard/teachers/{teacher_id}",
     *      tags={"Dashboard Api Teachers"},
     *     summary="update teacher",
     * @OA\Parameter(
     *          name="teacher_id",
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
    public function update(Request $request,$id)
    {
        $teacher = User::whereRoleIs('teacher')->where('id',$id)->first();
        if(!$teacher){
            return $this->sendError('The Teacher Not Fount');
        }
        //Validated
        $validate = Validator::make($request,[
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users,email,'.$teacher->id,
            'phone' => 'nullable|numeric|digits:11|unique:users,phone,'.$teacher->id,
            'active' => 'nullable|in:1,0',
            'permissions' => 'nullable|array|min:1',
            'permissions.*' => 'nullable|exists:permissions,name',
        ]);

        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $request_data = $validate->validated();
        unset($request_data['permissions']);
        $teacher->update($request_data);
        if ($request->permissions) $teacher->syncPermissions($request->permissions);

        return $this->sendResponse("Teacher Updated Successfully");
    }

    /**
     * @OA\Delete(
     *     path="/api/dashboard/teachers/{teacher_id}",
     *      tags={"Dashboard Api Teachers"},
     *     summary="Delete Teacher",
     *     @OA\Parameter(
     *         name="teacher_id",
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
    public function destroy($id)
    {
        $teacher = User::whereRoleIs('teacher')->where('id',$id)->first();
        if(!$teacher){
            return $this->sendError('The Teacher Not Fount');
        }
        if($teacher->image != 'users/default.webp' ||  $teacher->image){
            Storage::disk('public')->delete($teacher->image);
        }
        $teacher->delete();
        return $this->sendResponse("Deleted Data Successfully");
    }

}
