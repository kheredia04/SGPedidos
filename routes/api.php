<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

Route::prefix('orders')->middleware('auth:sanctum')->group(function () {
    Route::post('', [OrderController::class, 'store']);
    Route::get('', [OrderController::class, 'index']);
    Route::middleware('order.owner')->group(function () {
        Route::get('{id}', [OrderController::class, 'show']);
        Route::put('{id}/cancel', [OrderController::class, 'cancel']);
    });
});
