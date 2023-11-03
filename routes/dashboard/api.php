<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
    // routes students
    Route::apiResource('students', 'StudentController')->except(['edit','create']);
    // routes courses
    Route::apiResource('courses', 'CourseController')->except(['edit','create']);
    Route::put('/courses/{course}/approve','CourseController@approve');
    // routes listens
    Route::apiResource('listens', 'ListenController')->except(['edit','create']);
    Route::put('/listens/{listen}/approve','ListenController@approve');
    // routes subscriptions
    Route::apiResource('subscriptions', 'SubscriptionController')->except(['edit','create','update']);
    // Route::delete('/subscriptions/{student}/','SubscribeController@destroy');
    // routes levels
    Route::get('/levels','LevelController@index');

    Route::post('/logout', 'AuthController@logout');
});

// Route::post('/register','AuthController@register');
Route::post('/login','AuthController@login');
