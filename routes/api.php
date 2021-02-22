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
});

Route::group([
    'middleware' => 'auth.role:admin,dealer,subdealer',
    'prefix' => 'user'
], function ($router) {
    Route::post('/user-list', 'UserController@getUsers');
});

Route::group([
    'middleware' => 'auth.role:admin,dealer,subdealer',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/refresh', 'AuthController@refresh');
    Route::post('/user-profile', 'AuthController@userProfile');
    Route::post('/logout', 'AuthController@logout');
});

Route::group([
    'middleware' => 'auth.role:admin',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/register', 'AuthController@register');
    Route::post('/delete', 'AuthController@delete');
    Route::post('/restore', 'AuthController@restore');
});

Route::group([
    'middleware' => 'auth.role:admin,dealer,subdealer',
    'prefix' => 'product'
], function() {
    Route::post('/product-list', 'ProductController@getProductList');
    Route::post('/add-product', 'ProductController@addProduct');

    Route::post('/category-list', 'ProductCategoryController@getProductCategories');
    Route::post('/add-category', 'ProductCategoryController@addProductCategory');
});


// Route::resource('/inventory', 'InventoryController');

Route::group([
    'middleware' => 'auth.role:admin',
    'prefix' => 'inventory'
], function ($router) {
    Route::post('/create-inventory', 'InventoryController@createInventory');
});


Route::group([
    'middleware' => 'auth.role:admin,dealer,subdealer',
    'prefix' => 'inventory'
], function ($router) {
    Route::post('/', 'InventoryController@getInventory');
    Route::post('/user-inventory', 'InventoryController@getUserInventory');
    Route::post('/transfer-stock', 'InventoryController@transferStock');
});


Route::group([
    'middleware' => 'auth.role:admin,dealer,subdealer',
    // 'prefix' => ''
], function ($router) {
    Route::post('transaction', 'TransactionController@getTransactionList');
});


