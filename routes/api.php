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

Route::middleware('api')->group(function(){
    Route::post('/login', 'API\AuthController@login');
    Route::post('/signUp', 'API\AuthController@signUp');
    Route::post('/changePassword', 'API\AuthController@changePassword');
    Route::post('/updateUser', 'API\AuthController@updateUser');
    Route::get('/promotions', 'API\PromotionAPI@promotions');
    Route::post('/new_transaction', 'API\NewTransactionAPIController@store');
    Route::post('/new_transaction_withdraw', 'API\NewTransactionAPIController@postWithdraw');
    Route::get('/banks', 'API\BankAPIController@bankList');
    Route::get('/bonuses', 'API\BankAPIController@bonusList');
    Route::get('/bank_brand_list', 'API\BankAPIController@bankBrandList');
    Route::get('/kiosk_list', 'API\BankAPIController@kioskList');
    Route::get('/product_list', 'API\BankAPIController@productList');
    Route::post('/add_bank_detail', 'API\AuthController@addBankDetail');
    Route::get('/pages', 'API\PagesAPIController@pages');
    Route::get('/notices', 'API\NoticeAPIController@notices');
    Route::get('/history', 'API\ContactAPIController@history');
});
