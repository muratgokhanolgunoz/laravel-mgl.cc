<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\HomeController;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/blog/{language}/add', [BlogController::class, 'add'])->where('language', '[a-z]+');
Route::post('/blog/{language}/delete', [BlogController::class, 'delete'])->where('language', '[a-z]+');
Route::post('/career/add', [CareerController::class, 'add']);

Route::get('/language/{language}', [LanguageController::class, 'getLanguage'])->where('language', '[a-z]+');
Route::get('/blog/{language}', [BlogController::class, 'index'])->where('language', '[a-z]+');
Route::get('/career', [CareerController::class, 'index']);
Route::get('/home/select', [HomeController::class, 'selectDailyPhoto']);
Route::get('/{language}/home', [HomeController::class, 'index'])->where('language', '[a-z]+');



