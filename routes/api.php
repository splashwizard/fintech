<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', 'API\AuthController@login');
Route::post('/signUp', 'API\AuthController@signUp');
Route::post('/changePassword', 'API\AuthController@changePassword');
Route::middleware('api')->group(function(){
    Route::post('/updateUser', 'API\AuthController@updateUser');
    Route::get('/promotions', 'API\PromotionAPI@promotions');
    Route::post('/new_transaction', 'API\NewTransactionAPIController@store');
    Route::get('/banks', 'API\BankAPIController@bankList');
    Route::get('/bank_brand_list', 'API\BankAPIController@bankBrandList');
});
