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

// Route::group(["middleware"=>['auth:sanctum','abilities:student']],function(){
    Route::get('/profile','StudentController@profile');
    Route::post('/logout', 'AuthController@logout');
// });

Route::post('/login','AuthController@login');

