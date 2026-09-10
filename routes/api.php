<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SliderController;
use App\Http\Controllers\Api\SpecialCategoryController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('products', ProductController::class);
Route::apiResource('categories', CategoryController::class);
Route::get('sliders', [SliderController::class, 'index']);
Route::get('special-categories', [SpecialCategoryController::class, 'index']);
Route::get('search', [App\Http\Controllers\Api\SearchController::class, 'index']);
