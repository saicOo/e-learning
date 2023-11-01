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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
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

    public function show(User $user)
    {
        return response()->json([
            'status' => true,
            'data' => [
                'user' => $user,
            ]
        ], 200);
    }

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

    public function destroy(User $user)
    {
        $user->delete();
            return response()->json([
                'status' => true,
                'message' => 'Deleted Data Successfully',
            ], 200);
    }
}
