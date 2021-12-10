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

Route::post('mglBlog/add/{language}', [BlogController::class, 'add']);
Route::post('mglBlog/delete/{language}', [BlogController::class, 'delete']);
Route::post('mglBlog/update/{language}', [BlogController::class, 'update']);
Route::get('mglBlog/select/{language}/{id}', [BlogController::class, 'getSelectedBlog']);
Route::get('mglBlog/{language}/{itemsPerPage?}/{page?}', [BlogController::class, 'index']);
Route::post('mglCareer/add', [CareerController::class, 'add']);
Route::post('mglCareer/delete', [CareerController::class, 'delete']);
Route::get('mglCareer', [CareerController::class, 'index']);
Route::get('mglHome/select', [HomeController::class, 'selectDailyPhoto']);
Route::get('mglHome', [HomeController::class, 'index']);
Route::post('mglLog', [HomeController::class, 'userLogs']);
Route::get('mglGallery', [GalleryController::class, 'index']);
