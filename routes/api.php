<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
         Route::namespace("Api")    
            ->name("api")
//            ->prefix('api')
            ->group(function () {
                Route::get('/transfer', [\App\Http\Controllers\Api\FaceApiController::class,'passing_to_cpi'])->name('transfer');
                Route::get('/craw', [\App\Http\Controllers\Api\FaceApiController::class,'crawling_passing_attendance'])->name('craw');
                Route::get('/keepalive', [\App\Http\Controllers\Api\FaceApiController::class,'keep_alive'])->name('keepalive');
                Route::get('/keepalive2', [\App\Http\Controllers\Api\FaceApiController::class,'keep_alive'])->name('keepalive2');
            });
