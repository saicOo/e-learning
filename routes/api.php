<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

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

Route::group(["middleware"=>['auth:sanctum','abilities:student']],function(){
    Route::get('/profile','AuthController@profile');
    Route::post('/student/upload-image','AuthController@uploadImage');
    Route::post('/logout', 'AuthController@logout');
    Route::get('/lessons/{lesson}', 'LessonController@show');
    Route::get('/courses/{course}/lessons', 'LessonController@index');
    Route::get('/courses/{course}/progress', 'CourseController@courseProgress');


    Route::get('/lessons/{lesson}/start-quiz', 'QuizController@startQuiz');
    Route::post('/lessons/{lesson}/submit-quiz', 'QuizController@submitQuiz');
    Route::get('/courses/{course}/start-quiz', 'QuizController@startQuiz');
    Route::post('/courses/{course}/submit-quiz', 'QuizController@submitQuiz');
});

Route::controller(CourseController::class)->group(function () {
    Route::get('/courses', 'index');
    Route::get('/courses/{course}', 'show');
});

Route::controller(TeacherController::class)->group(function () {
    Route::get('/teachers', 'index');
    Route::get('/teachers/{teacher}', 'show');
});

Route::get('/categories','CategoryController@index');
Route::get('/students-highest-scores','StudentController@studentsHighestScores');
Route::get('/levels','LevelController@index');
Route::post('/contacts','ContactController@store');
Route::post('/login','AuthController@login');

