<?php

use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\OrderItemController;
use App\Http\Controllers\API\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', fn (Request $request) => $request->user());

Route::get('products', [ProductController::class, 'index']);

Route::prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'index']);
    Route::post('/', [CartController::class, 'store']);
    Route::put('/{id}', [CartController::class, 'update']);
    Route::delete('/{id}', [CartController::class, 'destroy']);
    Route::delete('/', [CartController::class, 'clear']);
});

Route::prefix('orders/{orderId}/items')->group(function () {
    Route::get('/', [OrderItemController::class, 'index']);
    Route::post('/', [OrderItemController::class, 'store']);
    Route::put('/{itemId}', [OrderItemController::class, 'update']);
    Route::delete('/{itemId}', [OrderItemController::class, 'destroy']);
});
