<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/upload', function () {
    return view('upload');
});
Route::post('/upload-video', 'VideoUploadController@upload');
Route::get('/forbidden', function () {
    return response()->json([
        'status_code' => 403,
        'success' => false,
        'message' => 'Not authorized.'
      ], 200);
});

Route::get('/clear', function() {

    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    // Artisan::call('route:cache');
    Artisan::call('config:cache');

    return "Cleared!";

 });

 Route::get('/run-seeder',function(){ Artisan::call('migrate:refresh',['--seed' => ' ']);Artisan::output();return 'Refresh Database Success'; });
