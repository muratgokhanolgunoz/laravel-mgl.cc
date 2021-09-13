<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\GalleryController;

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

Route::post('/blog/{language}/add', [BlogController::class, 'add'])->where('language', '[a-z]+');
Route::post('/blog/{language}/delete', [BlogController::class, 'delete'])->where('language', '[a-z]+');
Route::post('/career/add', [CareerController::class, 'add']);
Route::get('/blog/{language}', [BlogController::class, 'index'])->where('language', '[a-z]+');
Route::get('/career', [CareerController::class, 'index']);
Route::get('/home/select', [HomeController::class, 'selectDailyPhoto']);
Route::get('/mglHome', [HomeController::class, 'index']);
Route::post('/mglLog', [HomeController::class, 'userLogs']);
Route::get('{language}/getVideos', [GalleryController::class, 'index'])->where('language', '[a-z]+');
