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

Route::get('email/verify/{id}', 'VerificationController@verify')->name('verification.verify');

Route::get('email/resend', 'VerificationController@resend')->name('verification.resend');
Route::view('forgot_password', 'auth.reset_password')->name('password.reset');
