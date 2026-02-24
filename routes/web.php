<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\PenaltyController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'index'])->name('library.home');

Route::resource('auth', AuthController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);

Route::prefix('api')->group(function (): void {
    Route::get('/bootstrap', [LibraryController::class, 'bootstrap'])->name('api.bootstrap');

    Route::resources([
        'books' => BookController::class,
        'users' => UserController::class,
        'loans' => LoanController::class,
        'penalties' => PenaltyController::class,
        'admin' => AdminController::class,
    ]);
});
