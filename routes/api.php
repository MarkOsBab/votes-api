<?php

use App\Http\Controllers\Admin\CandidateController as AdminCandidateController;
use App\Http\Controllers\Admin\StatController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CandidateController;
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

        Route::group(['prefix' => 'candidates'], function() {
            Route::get('', [CandidateController::class, 'index']);
        });

        Route::group([
            'prefix' => 'dashboard',
            'middleware' => 'auth:api',
        ], function() {
            Route::get('stats', [StatController::class, 'index']);

            Route::prefix('candidates')->group(function() {
                Route::get('most-voted', [AdminCandidateController::class, 'getMostVoted']);
            });
        });
    });