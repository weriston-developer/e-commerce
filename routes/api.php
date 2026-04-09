<?php

use App\Infrastructure\Presentation\Controllers\AuthController;
use App\Infrastructure\Presentation\Controllers\EcommerceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Version 1
|--------------------------------------------------------------------------
|
| Rotas da API do E-commerce
| Prefixo: /api/v1
|
*/


// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'version' => 'v1',
    ]);
});

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::prefix('products')->group(function () {
    Route::get('/', [EcommerceController::class, 'listProducts']);
    Route::get('/active', [EcommerceController::class, 'activeProducts']);
    Route::get('/search', [EcommerceController::class, 'searchProducts']);
    Route::get('/price-range', [EcommerceController::class, 'productsByPriceRange']);
    Route::get('/category/{categoryUuid}', [EcommerceController::class, 'productsByCategory']);
    Route::get('/{uuid}', [EcommerceController::class, 'showProduct']);
});

Route::prefix('categories')->group(function () {
    Route::get('/', [EcommerceController::class, 'listCategories']);
    Route::get('/active', [EcommerceController::class, 'activeCategories']);
    Route::get('/{uuid}', [EcommerceController::class, 'showCategory']);
});

Route::middleware('auth:api')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    Route::prefix('users')->middleware('can:delete-users')->group(function () {
        Route::get('/', [AuthController::class, 'listUsers']);
        Route::delete('/{uuid}', [AuthController::class, 'deleteUser']);
    });

    Route::prefix('products')->middleware('can:manage-products')->group(function () {
        Route::post('/', [EcommerceController::class, 'createProduct']);
        Route::put('/{uuid}', [EcommerceController::class, 'updateProduct']);
        Route::delete('/{uuid}', [EcommerceController::class, 'deleteProduct']);
    });

    Route::prefix('categories')->middleware('can:manage-categories')->group(function () {
        Route::post('/', [EcommerceController::class, 'createCategory']);
        Route::put('/{uuid}', [EcommerceController::class, 'updateCategory']);
        Route::delete('/{uuid}', [EcommerceController::class, 'deleteCategory']);
    });
});
