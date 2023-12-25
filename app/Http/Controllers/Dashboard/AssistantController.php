<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Traits\PermissionsUser;

use App\Http\Controllers\BaseController as BaseController;

class AssistantController extends BaseController
{
    protected $userService;
    public function __construct(UserService $userService)
    {
        $this->middleware(['permission:assistants_update'])->only('update');
        $this->middleware(['permission:assistants_delete'])->only('destroy');
        $this->middleware(['permission:assistants_create'])->only('store');
        $this->middleware(['permission:assistants_approve'])->only('approve');
        $this->middleware(['checkApiAffiliation']);
        $this->userService = $userService;
    }
    use PermissionsUser;

    /**
     * @OA\Get(
     *     path="/api/dashboard/teachers/{teacher_id}/assistants",
     *      tags={"Dashboard Api Assistants"},
     *     summary="get all Assistants",
     * @OA\Parameter(
     *         name="teacher_id",
     *         in="path",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     * @OA\Parameter(
     *         name="publish",
     *         in="query",
     *         description="filter assistants with publish (publish , unpublish)",
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
    public function index(Request $request, $teacher_id)
    {
        $assistants = User::when($request->publish,function ($query) use ($request){ // if publish
            return $query->where('publish',$request->publish);
        })->when($request->search,function ($query) use ($request){ // if search
            return $query->where('name','Like','%'.$request->search.'%')
            ->OrWhere('email','Like','%'.$request->search.'%')
            ->OrWhere('phone','Like','%'.$request->search.'%');
        })->where("user_id",$teacher_id)->whereRoleIs('assistant')->get();

        return $this->sendResponse("",['assistants' => $assistants]);
    }

    /**
     * @OA\Post(
     *     path="/api/dashboard/assistants",
     *      tags={"Dashboard Api Assistants"},
     *     summary="Add New assistant",
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
        $request_data['user_id'] = $request->user()->id;
        $assistant = $this->userService->createUser($request_data);
        $assistant->attachRole('assistant');
        $assistant->syncPermissions($this->createPermissionsUser($assistant, 'assistant'));
        return $this->sendResponse('Assistant Created Successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/assistants/{assistant_id}",
     *      tags={"Dashboard Api Assistants"},
     *     summary="show assistant",
     *     @OA\Parameter(
     *         name="assistant_id",
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
    public function show($assistant_id)
    {
        $assistant = User::whereRoleIs('assistant')->where('id',$assistant_id)->first();
        if(!$assistant){
            return $this->sendError('The Assistant Not Fount');
        }
        $assistant->assistants;
        return $this->sendResponse("",['assistant' => $assistant]);
    }

    /**
     * @OA\Put(
     *     path="/api/dashboard/assistants/{assistant_id}",
     *      tags={"Dashboard Api Assistants"},
     *     summary="update assistant",
     * @OA\Parameter(
     *          name="assistant_id",
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
     *             @OA\Property(property="publish", type="boolen", example="integer"),
     *             @OA\Property(property="permissions", type="array", @OA\Items(
     *               type="string",example="assistant_create",
     *              ),),
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=404, description="Resource Not Found")
     * )
     */
    public function update(Request $request,$assistant_id)
    {
        $assistant = User::whereRoleIs('assistant')->where('id',$assistant_id)->first();
        if(!$assistant){
            return $this->sendError('The Assistant Not Fount');
        }
        //Validated
        $validate = Validator::make($request->all(),[
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:assistants,email,'.$assistant->id,
            'phone' => 'nullable|numeric|digits:11|unique:assistants,phone,'.$assistant->id,
            'permissions' => 'nullable|array|min:1',
            'permissions.*' => 'nullable|exists:permissions,name',
        ]);

        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }


        $request_data = $validate->validated();
        if ($request->permissions){
            unset($request_data['permissions']);
            $assistant->syncPermissions($request->permissions);
        }
        $assistant->update($request_data);

        return $this->sendResponse("Assistant Updated Successfully");
    }

    /**
     * @OA\Delete(
     *     path="/api/dashboard/assistants/{assistant_id}",
     *      tags={"Dashboard Api Assistants"},
     *     summary="Delete assistant",
     *     @OA\Parameter(
     *         name="assistant_id",
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
    public function destroy($assistant_id)
    {
        $assistant = User::whereRoleIs('assistant')->where('id',$assistant_id)->first();
        if(!$assistant){
            return $this->sendError('The Assistant Not Fount');
        }
        if($assistant->image != 'assistants/default.webp' ||  $assistant->image){
            Storage::disk('public')->delete($assistant->image);
        }
        $assistant->delete();
        return $this->sendResponse("Deleted Data Successfully");
    }

    /**
     * @OA\Put(
     *     path="/api/dashboard/assistants/{assistant_id}/approve",
     *      tags={"Dashboard Api Assistants"},
     *     summary="Approve Assistants",
     *     @OA\Parameter(
     *         name="assistant_id",
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
     *             @OA\Property(property="publish", type="boolen", example="publish or unpublish"),
     *         ),
     *     ),
     *       @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=404, description="Resource Not Found")
     *    )
     */
    public function approve(Request $request, $assistant_id)
    {
        $assistant = User::whereRoleIs('assistant')->where('id',$assistant_id)->first();
        if(!$assistant){
            return $this->sendError('The Assistant Not Fount');
        }
        //Validated
        $validate = Validator::make($request->all(),
        [
            'publish' => 'required|in:publish,unpublish',
        ]);

        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $assistant->update([
            'publish'=> $request->publish,
        ]);

        return $this->sendResponse("Assistant ".$request->publish." successfully");
    }

}
