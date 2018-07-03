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
Route::get('/test', function () {
    return view('test');
});

Route::get('/', function () {
    return view('home');
});

Route::get('/admin/{name}', function () {
    return view('home');
});

Route::get('/admin/{name}/{name2}', function () {
    return view('home');
});

Route::get('/login', function () {
    return view('welcome');
 });

