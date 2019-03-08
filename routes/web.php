<?php

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
    return redirect('admin');
//    return view('welcome');
});

Route::get('/update-password/{key}','UpdatePasswordController@Index');
Route::post('/update-password/{key}','UpdatePasswordController@UpdatePassword');
