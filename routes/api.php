<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('auth/login', 'Api\AuthController@login');
Route::post('auth/login/google', 'Api\AuthController@loginGoogle');

Route::group(['middleware' => ['apiJwt']], function () {
    Route::post('auth/logout', 'Api\AuthController@logout');
    Route::get('users', 'UserController@index');

    //Salão
    Route::get('saloes/teste','Api\SalaoController@home');
    Route::post('saloes/store', 'Api\SalaoController@store');
    Route::put('saloes/edit/{id}', 'Api\SalaoController@update');
    Route::get('saloes/show/{id}', 'Api\SalaoController@show');


    
});


