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
        // $this->middleware(['permission:assistants_read'])->only(['index','show']);
        // $this->middleware(['permission:assistants_create'])->only('store');
        // $this->middleware(['permission:assistants_update'])->only('update');
        // $this->middleware(['permission:assistants_delete'])->only('destroy');
        $this->userService = $userService;
    }
    use PermissionsUser;
    
    /**
     * @OA\Get(
     *     path="/api/dashboard/assistants",
     *      tags={"Dashboard Api Assistants"},
     *     summary="get all Assistants",
     * @OA\Parameter(
     *         name="active",
     *         in="query",
     *         description="filter assistants with active (active = 1 , not active = 0)",
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
        $assistants = User::whereRoleIs('assistant')->when($request->active,function ($query) use ($request){ // if active
            return $query->where('active',$request->active);
        })->when($request->search,function ($query) use ($request){ // if search
            return $query->where('name','Like','%'.$request->search.'%')
            ->OrWhere('email','Like','%'.$request->search.'%')
            ->OrWhere('phone','Like','%'.$request->search.'%');
        })->get();

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
     *             @OA\Property(property="assistant_id", type="integer", example="Sets the teacher assistant's ID"),
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
            'email' => 'required|string|max:255|email|unique:assistants,email',
            'password' => 'required|string|max:255|confirmed',
            'image' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg',
            'phone' => 'required|numeric|digits:11|unique:assistants,phone',
            'assistant_id'=> 'nullable|exists:assistants,id',
        ]);

        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $request_data = $validate->validated();
        if($request->image){
            $request_data['image'] = $this->uploadService->uploadImage('assistants', $request->image);
        }
        $assistant = $this->userService->createassistant($request_data);
        $assistant->attachRole('assistant');
        $assistant->syncPermissions($this->createPermissionsassistant($assistant, 'assistant'));
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
    public function show($id)
    {
        $assistant = User::whereRoleIs('assistant')->where('id',$id)->first();
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
     *             @OA\Property(property="active", type="boolen", example="integer"),
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
    public function update(Request $request,$id)
    {
        $assistant = User::whereRoleIs('assistant')->where('id',$id)->first();
        if(!$assistant){
            return $this->sendError('The Assistant Not Fount');
        }
        //Validated
        $validate = Validator::make($request,[
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:assistants,email,'.$assistant->id,
            'phone' => 'nullable|numeric|digits:11|unique:assistants,phone,'.$assistant->id,
            'active' => 'nullable|in:1,0',
            'permissions' => 'nullable|array|min:1',
            'permissions.*' => 'nullable|exists:permissions,name',
        ]);

        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $request_data = $validate->validated();
        unset($request_data['permissions']);
        $assistant->update($request_data);
        if ($request->permissions) $assistant->syncPermissions($request->permissions);
        
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
    public function destroy($id)
    {
        $assistant = User::whereRoleIs('assistant')->where('id',$id)->first();
        if(!$assistant){
            return $this->sendError('The Assistant Not Fount');
        }
        if($assistant->image != 'assistants/default.webp' ||  $assistant->image){
            Storage::disk('public')->delete($assistant->image);
        }
        $assistant->delete();
        return $this->sendResponse("Deleted Data Successfully");
    }


}
