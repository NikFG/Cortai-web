<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', 'Api\AuthController@login')->name('login');
    Route::post('login/google', 'Api\AuthController@loginGoogle');
    Route::post('user/create', 'UserController@store');
});


Route::group(['middleware' => ['apiJwt']], function () {
    Route::post('auth/logout', 'Api\AuthController@logout');
    Route::get('users', 'UserController@index');

    //Salão
    Route::group(['prefix' => 'saloes'], function () {
        Route::get('home', 'Api\SalaoController@home');
        Route::get('/cabeleireiros', 'Api\SalaoController@cabeleireiros');
        Route::post('store', 'Api\SalaoController@store');
        Route::post('edit/{id}', 'Api\SalaoController@update');
        Route::patch('edit/cabeleireiro/{email}', 'Api\SalaoController@adicionaCabeleireiro');
        Route::get('show/{id}', 'Api\SalaoController@show');
        Route::delete('destroy/{id}', 'Api\SalaoController@destroy');
        Route::patch('restore/{id}', 'Api\SalaoController@restore');
    });


    //Serviço
    Route::group(['prefix' => 'servicos'], function () {
        Route::get('/', 'Api\ServicoController@index');
        Route::get('/cabeleireiro', 'Api\ServicoController@indexAll');
        Route::get('/salao/{idSalao}', 'Api\ServicoController@servicoSalao');
        Route::post('store', 'Api\ServicoController@store');
        Route::post('edit/{id}', 'Api\ServicoController@update');
        Route::get('show/{id}', 'Api\ServicoController@show');
        Route::delete('destroy/{id}', 'Api\ServicoController@destroy');
        Route::patch('restore/{id}', 'Api\ServicoController@restore');
    });


    //Horário
    Route::group(['prefix' => 'horarios'], function () {
        Route::get('/cliente/{pago}', 'Api\HorarioController@clienteIndex');
        Route::get('/cabeleireiro/{confirmado}', 'Api\HorarioController@cabeleireiroIndex');
        Route::post('store', 'Api\HorarioController@store');
        Route::post('edit/{id}', 'Api\HorarioController@update');
        Route::get('show/{id}', 'Api\HorarioController@show');
        Route::delete('destroy/{id}', 'Api\HorarioController@destroy');
    });

    //Forma Pagamento
    Route::group(['prefix' => 'forma_pagamento'], function () {
        Route::get('/', 'Api\FormaPagamentoController@index');
        Route::post('store', 'Api\FormaPagamentoController@store');
        Route::post('edit/{id}', 'Api\FormaPagamentoController@update');
        Route::get('show/{id}', 'Api\FormaPagamentoController@show');
        Route::delete('destroy/{id}', 'Api\FormaPagamentoController@destroy');
        Route::patch('/link/{id}','Api\FormaPagamentoController@vincular');
        Route::patch('/unlink/{id}','Api\FormaPagamentoController@desvincular');
    });
    
    //Funcionamento
    Route::group(['prefix' => 'funcionamento'], function () {
        Route::get('/{id}', 'Api\FuncionamentoController@index');
        Route::get('show/{id}', 'Api\FuncionamentoController@show');
        Route::post('store', 'Api\FuncionamentoController@store');
        Route::post('edit/{id}', 'Api\FuncionamentoController@update');
        Route::delete('delete/{id}', 'Api\FuncionamentoController@destroy');
        Route::delete('deleteAll', 'Api\FuncionamentoController@destroyAll');
    });

/*    //Avaliação
    Route::group(['prefix' => 'avaliacoes'], function () {
        Route::get('/{id}','Api\AvaliacaoController@index');
    });*/

});

Route::get('agenda/{id}','Api\HorarioController@confirmaHorario');

Route::group(['prefix' => 'avaliacoes'], function () {
    Route::get('/{id}','Api\AvaliacaoController@index');
});

