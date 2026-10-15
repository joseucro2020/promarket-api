<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SliderController;
use App\Http\Controllers\Api\SpecialCategoryController;

// Rutas Públicas de Catálogo y Layout
Route::apiResource('products', ProductController::class);
Route::apiResource('categories', CategoryController::class);
Route::get('sliders', [SliderController::class, 'index']);
Route::get('special-categories', [SpecialCategoryController::class, 'index']);
Route::get('search', [\App\Http\Controllers\Api\SearchController::class, 'index']);
Route::get('home-layout', [\App\Http\Controllers\Api\HomeLayoutController::class, 'index']);
Route::get('bag-layout', [\App\Http\Controllers\Api\HomeLayoutController::class, 'bagLayout']);

// Rutas Públicas de Cobertura y Recogida
Route::post('addresses/check-coverage', [\App\Http\Controllers\AddressController::class, 'checkCoverage']);
Route::get('pickup-locations', [\App\Http\Controllers\AddressController::class, 'getPickupLocations']);

// Rutas de Autenticación Social (Google y Facebook)
Route::post('auth/social-login', [\App\Http\Controllers\Api\AuthController::class, 'socialLogin']);

// Rutas Protegidas por Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::put('/user', [\App\Http\Controllers\Api\AuthController::class, 'updateProfile']);
    Route::post('auth/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);
    Route::apiResource('addresses', \App\Http\Controllers\AddressController::class);
});
