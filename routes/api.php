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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/v1/diffusions', \App\Http\Controllers\Diffusions\IndexController::class);
    Route::post('/v1/diffusions', \App\Http\Controllers\Diffusions\CreateController::class);
    Route::get('/v1/diffusions/{diffusion}', \App\Http\Controllers\Diffusions\DetailController::class);
});
