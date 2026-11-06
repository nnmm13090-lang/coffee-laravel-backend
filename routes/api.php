<?php

use App\Http\Controllers\Api\V1\Admin;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CouponController;
use App\Http\Controllers\Api\V1\FavoriteController;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // ---- Public ----
    Route::middleware('throttle:10,1')->prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
    });

    Route::get('home', HomeController::class);
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{id}', [ProductController::class, 'show'])->whereNumber('id');

    // ---- Any logged-in user (customer, staff, admin) ----
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);

        Route::get('me', [ProfileController::class, 'show']);
        Route::post('me', [ProfileController::class, 'update']);

        Route::get('favorites', [FavoriteController::class, 'index']);
        Route::post('favorites/{product}', [FavoriteController::class, 'toggle']);

        Route::post('coupons/apply', [CouponController::class, 'apply']);

        Route::get('orders', [OrderController::class, 'index']);
        Route::post('orders', [OrderController::class, 'store']);
        Route::get('orders/{id}', [OrderController::class, 'show'])->whereNumber('id');
    });

    // ---- Staff / Admin (permission based) ----
    // auth:sanctum must come first so the permission middleware sees the token user.
    Route::prefix('admin')->name('admin.')->middleware('auth:sanctum')->group(function () {

        // Staff + Admin
        Route::middleware('permission:orders.view-all')->group(function () {
            Route::get('orders', [Admin\OrderController::class, 'index']);
            Route::get('orders/{id}', [Admin\OrderController::class, 'show'])->whereNumber('id');
        });
        Route::patch('orders/{id}/status', [Admin\OrderController::class, 'updateStatus'])
            ->whereNumber('id')
            ->middleware('permission:orders.update-status');

        // Admin only
        Route::apiResource('products', Admin\ProductController::class)
            ->except('show')->middleware('permission:products.manage');
        Route::apiResource('categories', Admin\CategoryController::class)
            ->except('show')->middleware('permission:categories.manage');
        Route::apiResource('banners', Admin\BannerController::class)
            ->except('show')->middleware('permission:banners.manage');
        Route::apiResource('coupons', Admin\CouponController::class)
            ->except('show')->middleware('permission:coupons.manage');

        Route::middleware('permission:users.manage')->group(function () {
            Route::get('users', [Admin\UserController::class, 'index']);
            Route::put('users/{user}/role', [Admin\UserController::class, 'updateRole']);
            Route::get('roles', [Admin\UserController::class, 'roles']);
        });
    });
});
