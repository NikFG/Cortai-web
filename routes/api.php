<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


Route::post('auth/login', 'Api\AuthController@login')->name('login');
Route::post('auth/login/google', 'Api\AuthController@loginGoogle');
Route::post('auth/user/create', 'UserController@store');

Route::group(['middleware' => ['apiJwt']], function () {
    Route::post('auth/logout', 'Api\AuthController@logout');
    Route::get('users', 'UserController@index');

    //Salão
    Route::get('saloes/home','Api\SalaoController@home');
    Route::post('saloes/store', 'Api\SalaoController@store');
    Route::put('saloes/edit/{id}', 'Api\SalaoController@update');
    Route::get('saloes/show/{id}', 'Api\SalaoController@show');

    //Serviço
    Route::get('servicos', 'Api\ServicoController@index');
    Route::post('servicos/store', 'Api\ServicoController@store');
    Route::put('servicos/edit/{id}', 'Api\ServicoController@update');
    Route::get('servicos/show/{id}', 'Api\ServicoController@show');

    
});


