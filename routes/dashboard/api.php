<?php

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\QuestionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(["middleware"=>['auth:sanctum','abilities:user']],function(){
    // routes users
    Route::apiResource('users', 'UserController')->except(['edit','create']);
    Route::put('users/{user}/change-password', 'UserController@changePassword');
    // routes students
    Route::apiResource('students', 'StudentController')->except(['edit','create']);
    Route::put('students/{student}/change-password', 'StudentController@changePassword');
    // routes courses
    Route::apiResource('courses', 'CourseController')->except(['edit','create']);
    Route::put('/courses/{course}/approve','CourseController@approve');
    // routes listens
    Route::apiResource('listens', 'ListenController')->except(['edit','create']);
    Route::put('/listens/{listen}/approve','ListenController@approve');
    Route::post('/listens/{listen}/upload-video','ListenController@uploadVideo');
    Route::post('/listens/{listen}/upload-file','ListenController@uploadFile');
    // routes questions
    Route::apiResource('questions', 'QuestionController')->except(['edit','create']);
    Route::controller(QuestionController::class)->group(function () {
        Route::get('courses/{course}/questions', 'index');
        Route::post('courses/{course}/questions', 'store');
        Route::put('questions/{question}', 'update');
        Route::delete('questions/{question}', 'destroy');
    });
    // routes category
    Route::apiResource('categories', 'CategoryController')->only(['index','store','update','delete']);
    // routes subscriptions
    Route::apiResource('subscriptions', 'SubscriptionController')->except(['edit','create','update']);
    // Route::delete('/subscriptions/{student}/','SubscribeController@destroy');
    // routes levels
    Route::get('/levels','LevelController@index');


    Route::post('/logout', 'AuthController@logout');
});

// Route::post('/register','AuthController@register');
Route::post('/login','AuthController@login');
Route::get('/roles',function () {
    return response()->json([
        'status' => true,
        'data' => [
            'roles' => Role::all(),
        ]
    ], 200);
});
Route::get('/permissions',function () {
    return response()->json([
        'status' => true,
        'data' => [
            'permissions' => Permission::all(),
        ]
    ], 200);
});
