<?php

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

Route::post('/v1/login', \App\Http\Controllers\Api\LoginController::class);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/v1/logout', \App\Http\Controllers\Api\LogoutController::class);

    Route::get('/v1/diffusions', \App\Http\Controllers\Api\Diffusions\IndexController::class);
    Route::post('/v1/diffusions', \App\Http\Controllers\Api\Diffusions\CreateController::class);
    Route::get('/v1/diffusions/{diffusion}', \App\Http\Controllers\Api\Diffusions\DetailController::class);
});
