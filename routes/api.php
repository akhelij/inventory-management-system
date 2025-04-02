<?php

use App\Http\Controllers\API\V1\ProductController;
use App\Http\Controllers\API\ProductController as ApiProductController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\OrderItemController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('products/', [ProductController::class, 'index'])->name('api.product.index');

// Product API endpoints
Route::get('/products', [ApiProductController::class, 'index']);

// Cart API endpoints
Route::get('/cart', [CartController::class, 'index']);
Route::post('/cart', [CartController::class, 'store']);
Route::put('/cart/{id}', [CartController::class, 'update']);
Route::delete('/cart/{id}', [CartController::class, 'destroy']);
Route::delete('/cart', [CartController::class, 'clear']);

// Order items API endpoints
Route::get('/orders/{orderId}/items', [OrderItemController::class, 'index']);
Route::post('/orders/{orderId}/items', [OrderItemController::class, 'store']);
Route::put('/orders/{orderId}/items/{itemId}', [OrderItemController::class, 'update']);
Route::delete('/orders/{orderId}/items/{itemId}', [OrderItemController::class, 'destroy']);
