<?php

use App\Models\Role;
use App\Models\Permission;
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
// routes teacher
Route::group(["middleware"=>['auth:sanctum','abilities:user']],function(){

    // routes teachers
    Route::resource('teachers', 'TeacherController')->except(['edit','create']);
    // routes assistants
    Route::resource('assistants', 'AssistantController')->except(['edit','create']);
    // routes users
    Route::put('/users/change-password', 'UserController@changePassword');
    Route::post('/users/upload-image', 'UserController@uploadImage');
    // routes students
    Route::apiResource('students', 'StudentController')->except(['edit','create']);
    Route::put('/students/{student}/change-password', 'StudentController@changePassword');
    // routes courses
    Route::apiResource('courses', 'CourseController')->except(['edit','create']);
    Route::put('/courses/{course}/approve','CourseController@approve');
    // routes lessons
    Route::controller(LessonController::class)->group(function () {
        Route::get('/courses/{course}/lessons', 'index');
        Route::post('/courses/{course}/lessons', 'store');
        Route::get('/lessons/{lesson}', 'show');
        Route::put('/lessons/{lesson}', 'update');
        Route::delete('lessons/{lesson}', 'destroy');
        Route::put('/lessons/{lesson}/approve', 'approve');
        Route::post('/lessons/{lesson}/upload-video', 'uploadVideo');
        Route::post('/lessons/{lesson}/upload-file', 'uploadFile');
    });
    // routes questions
    Route::controller(QuestionController::class)->group(function () {
        Route::get('/courses/{course}/questions', 'index');
        Route::post('/courses/{course}/questions', 'store');
        Route::delete('questions/{question}', 'destroy');
    });
    // routes QuizAttempt
    Route::controller(QuizAttemptController::class)->group(function () {
        Route::get('/quizzes/{quiz}/quiz-attempts', 'index');
        Route::get('/quiz-attempts/{quizAttempt}', 'show');
        Route::put('/quiz-attempts/{quizAttempt}', 'store');
    });
    Route::controller(QuizController::class)->group(function () {
        Route::get('/courses/{course}/quizzes', 'index');
        Route::post('/courses/{course}/quizzes', 'store');
        Route::get('/quizzes/{quiz}', 'show');
        Route::delete('/quizzes/{quiz}', 'destroy');
    });
    // routes contacts
Route::apiResource('contacts', 'ContactController')->only(['index','destroy']);
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
