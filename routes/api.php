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

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/login', 'AuthController@login');
    Route::post('/register', 'AuthController@register');
    Route::post('/refresh', 'AuthController@refresh');
    Route::post('/user-profile', 'AuthController@userProfile');
    Route::post('/logout', 'AuthController@logout');
    Route::post('/delete', 'AuthController@delete');
    Route::post('/restore', 'AuthController@restore');
});

Route::get('products', [
    'middleware' => 'auth.role:admin',
    'uses' => 'ProductController@index'
]);

Route::resource('/product-category', 'ProductCategoryController');
Route::resource('/inventory', 'InventoryController');
