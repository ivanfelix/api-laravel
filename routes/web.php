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
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Rutas de Usuario
|--------------------------------------------------------------------------
*/
Route::post('/login', 'UserController@login');
Route::post('/register', 'UserController@register');
Route::put('/user/update', 'UserController@update');

// ejemplo con middleware - El se incluye en el archivo Kernel.php 
Route::post('/user/upload', 'UserController@upload')->middleware('apiauth');
// Se pasan paramentros por la url
Route::get('/user/avatar/{filename}', 'UserController@getImage');
Route::get('/user/{filename}', 'UserController@getUser');

Route::resource('/category', 'CategoryController');

Route::resource('/posts', 'PostController');

Route::get('/posts/category/{filename}', 'PostController@getPostsByCategory');
Route::get('/posts/user/{filename}', 'PostController@getPostsByUser');