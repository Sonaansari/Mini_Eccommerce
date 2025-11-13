<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;


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



Route::post('register',[AuthController::class,'register']);
Route::post('login',[AuthController::class,'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout',[AuthController::class,'logout']);

    Route::get('products',[ProductController::class,'index']);
    Route::get('products/{product}',[ProductController::class,'show']);

    // Cart
    Route::get('cart',[CartController::class,'index']);
    Route::post('cart/add',[CartController::class,'add']);
    Route::delete('cart/{id}',[CartController::class,'remove']);

    // Orders
    Route::post('orders',[OrderController::class,'store']);
    Route::get('orders',[OrderController::class,'index']);
});

// Admin routes
Route::middleware(['auth:sanctum','admin'])->group(function(){
    Route::post('products',[ProductController::class,'store']);
    Route::put('products/{product}',[ProductController::class,'update']);
    Route::post('orders/{order}/status',[OrderController::class,'updateStatus']);
});
