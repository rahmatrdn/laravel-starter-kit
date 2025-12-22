<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;

Route::get('/', function () {
    return view('_admin.dashboard');
})->name('admin.dashboard');

// Admin Users Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/add', [UserController::class, 'add'])->name('add');
        Route::post('/create', [UserController::class, 'doCreate'])->name('create');
        Route::get('/update/{id}', [UserController::class, 'update'])->name('update');
        Route::post('/update/{id}', [UserController::class, 'doUpdate'])->name('doUpdate');
        Route::delete('/delete/{id}', [UserController::class, 'delete'])->name('delete');
        Route::post('/reset-password/{id}', [UserController::class, 'resetPassword'])->name('resetPassword');
    });
});
