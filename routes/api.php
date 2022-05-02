<?php

use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransactionDetailController;
use App\Http\Controllers\Admin\AdminBookController;
use App\Http\Controllers\Admin\AdminCategorieController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BuyController;
use App\Http\Controllers\DetailbuyController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::resource('adminbooks', AdminBookController::class);
Route::resource('admincategories', AdminCategorieController::class);
Route::resource('users', UserController::class);
Route::resource('buys', BuyController::class);
Route::resource('detailbuys', DetailbuyController::class);

//transactions Paths
Route::resource('transactions', TransactionController::class);
Route::resource('transactiondetails', TransactionDetailController::class);

Route::get('/recomended', [AdminBookController::class, 'recomended'])->name('recomended');

Route::group(['prefix' => 'auth'], function ($router) {

    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/me', [AuthController::class, 'me']);

});

Route::get('/userget', function () {
    return auth()->check()? auth()->user(): null;
})->name('userget');
