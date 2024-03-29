<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', 'Api\AuthController@login')->name('login');
    Route::post('login/google', 'Api\AuthController@loginGoogle');
    Route::post('user/create', 'UserController@store');
    Route::post('login/reset', 'Api\AuthController@resetPassword');

});
Route::post('password/reset', 'Api\AuthController@reset');
Route::get('termos', 'UserController@termos');

Route::group(['middleware' => ['apiJwt']], function () {

    Route::post('auth/logout', 'Api\AuthController@logout');
    Route::post('auth/edit/{id}', 'UserController@update');
    Route::post('auth/edit/imagem/{id}', 'UserController@updateImage');

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
        Route::get('/cabeleireiro', 'Api\HorarioController@cabeleireiroIndex');
        Route::get('/calendario', 'Api\HorarioController@calendario');
        Route::get('/cabeleireiro/{cabeleireiro_id}/data/{data}', 'Api\HorarioController@agenda');
        Route::post('store', 'Api\HorarioController@store');
        Route::post('edit/{id}', 'Api\HorarioController@update');
        Route::get('show/{id}', 'Api\HorarioController@show');
        Route::get('count/{id}', 'Api\HorarioController@conta');
        Route::delete('destroy/{id}', 'Api\HorarioController@destroy');
        Route::put('confirma/{id}', 'Api\HorarioController@confirmaHorario');
        Route::put('cancela/{id}', 'Api\HorarioController@cancelaHorario');
        Route::put('paga/{id}', 'Api\HorarioController@confirmaPagamento');
    });

    //Forma Pagamento
    Route::group(['prefix' => 'formaPagamento'], function () {
        Route::get('/', 'Api\FormaPagamentoController@index');
        Route::get('/{salao_id}', 'Api\FormaPagamentoController@indexSalao');
        Route::post('store', 'Api\FormaPagamentoController@store');
    });

    //Funcionamento
    Route::group(['prefix' => 'funcionamento'], function () {
        Route::get('/{id}', 'Api\FuncionamentoController@index');
        Route::get('/{dia_semana}/{salao_id}', 'Api\FuncionamentoController@indexDiaSemana');
        Route::get('show/{id}', 'Api\FuncionamentoController@show');
        Route::post('store', 'Api\FuncionamentoController@store');
        Route::post('edit/{id}', 'Api\FuncionamentoController@update');
        Route::delete('delete/{id}', 'Api\FuncionamentoController@destroy');
        Route::delete('deleteAll', 'Api\FuncionamentoController@destroyAll');
    });

    Route::group(['prefix' => 'avaliacoes'], function () {
        Route::get('/{id}', 'Api\AvaliacaoController@index');
        Route::post('/store', 'Api\AvaliacaoController@store');
    });

    Route::group(['prefix' => 'galeria'], function () {
        Route::get('/{salao_id} ', 'Api\GaleriaController@index');
        Route::post('/store', 'Api\GaleriaController@store');
    });


});

