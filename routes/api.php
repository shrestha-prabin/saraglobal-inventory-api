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

/**
 * Auth
 */
Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function () {
    Route::post('/login', 'AuthController@login');
    Route::post('/reset-password', 'AuthController@resetPassword');
});

Route::group([
    'middleware' => 'auth.role:admin,dealer,subdealer',
    'prefix' => 'auth'
], function () {
    Route::post('/register', 'AuthController@register');
    Route::post('/refresh', 'AuthController@refresh');
    Route::post('/user-profile', 'AuthController@userProfile');
    Route::post('/logout', 'AuthController@logout');
    Route::post('/change-password', 'AuthController@changePassword');
});

Route::group([
    'middleware' => 'auth.role:admin',
    'prefix' => 'auth'
], function () {
    Route::post('/delete', 'AuthController@delete');
    Route::post('/restore', 'AuthController@restore');
});

/**
 * User
 */

Route::group([
    'middleware' => 'auth.role:admin',
    'prefix' => 'user'
], function () {
    Route::post('/user-list', 'UserController@getUserList');
});


Route::group([
    'middleware' => 'auth.role:admin,dealer,subdealer',
    'prefix' => 'user'
], function () {
    Route::post('/client-list', 'UserController@getClientList');
});

/**
 * Product
 */
Route::group([
    'middleware' => 'auth.role:admin,dealer,subdealer',
    'prefix' => 'product'
], function () {
    Route::post('/product-list', 'ProductController@getProductList');
    Route::post('/add-product', 'ProductController@addProduct');
    Route::post('/product-details', 'ProductController@getProductDetails');


    Route::post('/category-list', 'ProductCategoryController@getProductCategories');
    Route::post('/add-category', 'ProductCategoryController@addProductCategory');
    Route::post('/category-details', 'ProductCategoryController@getCategoryDetails');
});


Route::group([
    'middleware' => 'auth.role:admin,dealer,subdealer',
    'prefix' => 'product'
], function () {
    Route::post('/transaction-history', 'ProductController@getProductTransactionHistory');
});


/**
 * Inventory
 */
Route::group([
    'middleware' => 'auth.role:admin',
    'prefix' => 'inventory'
], function () {
    Route::post('/add-inventory', 'InventoryController@addInventory');
    Route::post('/all-inventory', 'InventoryController@getInventory');
});


Route::group([
    'middleware' => 'auth.role:admin,dealer,subdealer',
    'prefix' => 'inventory'
], function () {
    Route::post('/user-inventory', 'InventoryController@getUserInventory');
    Route::post('/transfer-inventory', 'InventoryController@transferInventory');
    Route::post('/item-details', 'InventoryController@getInventoryItemDetails');

    Route::post('/product-stock', 'InventoryController@getProductStock');
    Route::post('/category-stock', 'InventoryController@getCategoryStock');
});


/**
 * Transaction
 */
Route::group([
    'middleware' => 'auth.role:admin,dealer,subdealer',
    'prefix' => 'transaction'
], function () {
    Route::post('/transaction-list', 'TransactionController@getTransactionList');
    Route::post('/transaction-details', 'TransactionController@getTransactionDetails');
});

Route::group([
    'middleware' => 'auth.role:admin',
    'prefix' => 'notification'
], function () {
    Route::post('/all', 'NotificationController@getNotifications');
});