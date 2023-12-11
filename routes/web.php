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
    Artisan::call('config:cache');
    Artisan::call('view:clear');

    return "Cleared!";

 });

 Route::get('/run-seeder',function(){ Artisan::call('migrate:refresh',['--seed' => ' ']);Artisan::output();return 'Refresh Database Success'; });
