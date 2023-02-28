<?php

use Illuminate\Support\Facades\Route;

/*
  |--------------------------------------------------------------------------
  | Web Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register web routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | contains the "web" middleware group. Now create something great!
  |
 */


Route::namespace("Web")
        ->name("web")
        ->prefix('report')
        ->group(function () {
            Route::get('/', [\App\Http\Controllers\Web\FaceController::class, 'report_formatted'])->name('report');
            Route::get('/raw', [\App\Http\Controllers\Web\FaceController::class, 'report'])->name('report_raw');
            Route::get('/data', [\App\Http\Controllers\Web\FaceController::class, 'getData'])->name('data');
            Route::get('/data_beautifullify', [\App\Http\Controllers\Web\FaceController::class, 'getDataFormatted'])->name('data_pretty');
        });
//Route::namespace("Web")
//        ->name("web")
//        ->prefix('report')
//        ->group(function () {
//            Route::get('/', [\App\Http\Controllers\Web\ExportController::class, 'index'])->name('report');
//            Route::get('/draw', [\App\Http\Controllers\Web\ExportController::class, 'draw'])->name('report_draw');
//        });
//            Route::resource('report', \App\Http\Controllers\Web\ExportController::class);
Route::get('', [\App\Http\Controllers\Web\SettingController::class, 'index'])->name('index');
Route::get('/home', [\App\Http\Controllers\Web\SettingController::class, 'index'])->name('index');
Route::resource('setting', \App\Http\Controllers\Web\SettingController::class);
Route::get('sendsap', [\App\Http\Controllers\Web\SendSapController::class,'index'])->name('sendsapindex');
Route::post('sendsap.transfer', [\App\Http\Controllers\Web\SendSapController::class, 'transfer_sap'])->name('sendsap.transfer');
Route::post('setting.check', [\App\Http\Controllers\Web\SettingController::class, 'check_connection'])->name('setting.check');

