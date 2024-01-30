<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserService
{
    public function createUser($data)
    {
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        return $user;
    }

    // public function validator($request, $data, $role = null)
    // {
    //     $rules = [
    //         'name' => 'nullable|string|max:255',
    //         'email' => 'nullable|string|email|max:255|unique:users,email,'.$data->id,
    //         'phone' => 'nullable|numeric|digits:11|unique:users,phone,'.$data->id,
    //         'permissions' => 'nullable|array|min:1',
    //         'permissions.*' => 'nullable|exists:permissions,name',
    //     ];
    //     // if($role == "teacher"){
    //     //     $role += ['user_id' => 'nullable|string|email|max:255|unique:users,email,'.$data->id,];
    //     // }
    //     //Validated
    //     $validate = Validator::make($request,$rules);
    //     return $validate;
    // }
}
