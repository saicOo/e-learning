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

    // routes teachers teachers
    Route::controller(TeacherController::class)->group(function () {
        Route::get('/teachers', 'index');
        Route::post('/teachers', 'store');
        Route::get('/teachers/{teacher}', 'show');
        Route::put('/teachers/{teacher}', 'update');
        Route::delete('teachers/{teacher}', 'destroy');
        Route::put('/teachers/{teacher}/approve', 'approve');

    });
    // routes assistants
    Route::controller(AssistantController::class)->group(function () {
        Route::get('/teachers/{teacher}/assistants', 'index');
        Route::post('/assistants', 'store');
        Route::get('/assistants/{assistant}', 'show');
        Route::put('/assistants/{assistant}', 'update');
        Route::delete('assistants/{assistant}', 'destroy');
        Route::put('/assistants/{assistant}/approve', 'approve');
    });
    // routes users
    Route::put('/users/change-password/{user}', 'UserController@changePassword');
    Route::post('/users/upload-image/{user}', 'UserController@uploadImage');
    // routes students
    Route::apiResource('students', 'StudentController')->except(['edit','create']);
    Route::put('/students/{student}/change-password', 'StudentController@changePassword');
    Route::put('/students/{student}/approve', 'StudentController@approve');
    // routes courses
    Route::controller(CourseController::class)->group(function () {
        Route::get('/courses', 'index');
        Route::post('/teachers/{teacher}/courses', 'store');
        Route::get('/courses/{course}', 'show');
        Route::post('/courses/{course}/upload-image', 'uploadImage');
        Route::put('/courses/{course}', 'update');
        Route::delete('courses/{course}', 'destroy');
        Route::put('/courses/{course}/approve', 'approve');
    });
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
    // routes offline exams
    Route::controller(OfflineExamController::class)->group(function () {
        Route::post('/sessions/{session}/offline-exams', 'store');
        Route::delete('offline-exams/{offlineExam}', 'destroy');
    });
    // routes notifications
    Route::controller(NotificationController::class)->group(function () {
        Route::get('/notifications', 'index');
        Route::post('/notifications', 'markAsRead');
    });
    // routes questions
    Route::controller(QuestionController::class)->group(function () {
        Route::get('/questions', 'index');
        Route::post('/questions', 'store');
        Route::delete('questions/{question}', 'destroy');
        Route::post('/questions/{question}/upload-video', 'uploadVideo');
    });
    // routes QuizAttempt
    Route::controller(QuizAttemptController::class)->group(function () {
        Route::get('/courses/{course}/quiz-attempts', 'index');
        Route::get('/quiz-attempts/{quizAttempt}', 'show');
        Route::put('/quiz-attempts/{quizAttempt}', 'update');
    });
    Route::controller(QuizController::class)->group(function () {
        Route::get('/quizzes', 'index');
        Route::post('/quizzes', 'store');
        Route::get('/quizzes/{quiz}', 'show');
        Route::delete('/quizzes/{quiz}', 'destroy');
        // Route::put('/quizzes/{quiz}/approve', 'approve');
    });
    Route::controller(SessionController::class)->group(function () {
        Route::get('/courses/{course}/sessions', 'index');
        Route::post('/courses/{course}/sessions', 'store');
        Route::get('/sessions/{session}', 'show');
        Route::put('/sessions/{session}', 'update');
        Route::delete('/sessions/{session}', 'destroy');
    });
    // routes contacts
    Route::apiResource('contacts', 'ContactController')->only(['index','update','destroy']);
    // routes category
    Route::apiResource('categories', 'CategoryController')->only(['index','store','update','destroy']);
    // routes subscriptions
    Route::apiResource('subscriptions', 'SubscriptionController')->only(['index','store','show','destroy']);
    // routes levels
    Route::get('/levels','LevelController@index');

    Route::group(["prefix"=>'report',"namespace"=>'Reports',],function(){
        Route::get('/subscriptions','SubscriptionController@subscriptionReport');
        });


    Route::group(["prefix"=>'courses',"namespace"=>'Course',],function(){
        Route::post('{course}/quizzes','QuizController@store');
        });
    Route::group(["prefix"=>'lessons',"namespace"=>'Lesson',],function(){
        Route::post('{lesson}/quizzes','QuizController@store');
        });
        Route::group(["prefix"=>'report',"namespace"=>'Reports',],function(){
            Route::get('/courses/{course}/students-score','StudentController@studentsScore');
            });
    Route::post('/logout', 'AuthController@logout');

});


// Route::post('/register','AuthController@register');
Route::post('/login','AuthController@login');
Route::get('/roles',function () {
    return response()->json([
        'success' => true,
        'data' => [
            'roles' => Role::all(),
        ]
    ], 200);
});
Route::get('/permissions',function () {
    return response()->json([
        'success' => true,
        'data' => [
            'permissions' => Permission::all(),
            'models' => ['teachers','assistants','students','courses'
            ,'lessons','subscriptions','contacts','categories','quizzes','questions']
        ]
    ], 200);
});
