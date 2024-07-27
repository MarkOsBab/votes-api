<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\VoteController;

Route::middleware('api')
    ->group(function() {
        Route::group(['prefix' => 'auth'], function() {
            Route::post('/login', [AuthController::class, 'login'])->name('login');
            Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api')->name('logout');
            Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api')->name('refresh');
            Route::get('/me', [AuthController::class, 'me'])->middleware('auth:api')->name('me');
        });

        Route::group(['prefix' => 'votes'], function() {
            Route::get('', [VoteController::class, 'index'])->name('index');
            Route::post('', [VoteController::class, 'store'])->name('store');
        });
    });