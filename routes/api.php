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

Route::post('/mglBlog/add/{language}', [BlogController::class, 'add'])->where('language', '[a-z]+');
Route::post('/mglBlog/delete/{language}', [BlogController::class, 'delete'])->where('language', '[a-z]+');
Route::post('/mglBlog/update/{language}', [BlogController::class, 'update'])->where('language', '[a-z]+');
Route::get('/mglBlog/{language}', [BlogController::class, 'index'])->where('language', '[a-z]+');
Route::get('/mglBlog/{language}/{id}/', [BlogController::class, 'getSelectedBlog'])->where(['language' => '[a-z]+', 'id' => '[0-9]+']);

Route::post('/mglCareer/add', [CareerController::class, 'add']);
Route::get('/mglCareer', [CareerController::class, 'index']);

Route::get('/mglHome/select', [HomeController::class, 'selectDailyPhoto']);
Route::get('/mglHome', [HomeController::class, 'index']);
Route::post('/mglLog', [HomeController::class, 'userLogs']);

Route::get('/mglGetVideos/{language}', [GalleryController::class, 'index'])->where('language', '[a-z]+');
