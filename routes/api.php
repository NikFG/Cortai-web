<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('auth/login', 'Api\AuthController@login');
Route::post('auth/login/google', 'Api\AuthController@loginGoogle');

Route::group(['middleware' => ['apiJwt']], function () {
    Route::post('auth/logout', 'Api\AuthController@logout');
    Route::get('users', 'UserController@index');

    Route::post('saloes/store', 'Api\SalaoController@store');
    Route::get('saloes', 'Api\SalaoController@index');
    Route::put('saloes/edit/{id}', 'Api\SalaoController@update');
    Route::delete('saloes/delete/{id}', 'Api\SalaoController@destroy');
    Route::get('saloes/show/{id}', 'Api\SalaoController@show');
});
//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});
