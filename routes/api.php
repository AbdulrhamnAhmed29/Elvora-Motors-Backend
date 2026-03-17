<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Root Route (Health Check)
Route::get('/', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Laravel API is running',
        'service' => 'Backend',
        'time' => now()
    ]);
});

// Optional: Test Route
Route::get('/test', function () {
    return 'Server is working correctly';
});

// ---------- Auth Routes ----------
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// ---------- Public Products (everyone can see) ----------
Route::get('/products', [ProductsController::class, 'index']);       // Show all products
Route::get('/products/{id}', [ProductsController::class, 'getbyId']); // Show one product by id

// ---------- Protected Routes ----------
Route::middleware('auth:sanctum')->group(function () {

    // ---------- Current User ----------
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    // ---------- Admin Routes ----------
    Route::middleware('admin')->group(function () {

        // Users Management
        Route::prefix('user')->group(function () {
            Route::get('show', [UserController::class, 'index']);
            Route::post('create', [UserController::class, 'store']);
            Route::get('show/{id}', [UserController::class, 'show']);
            Route::post('update/{id}', [UserController::class, 'update']);
            Route::delete('delete/{id}', [UserController::class, 'destroy']);
            Route::get('dashboard/stats', [UserController::class, 'getDashboardStats']);
        });

        // Products Management (CRUD + stats)
        Route::prefix('product')->controller(ProductsController::class)->group(function () {
            Route::get('show', 'index');           // 👈 Show all products for admin
            Route::get('showbyid/{id}', 'getbyId');// 👈 Show one product for admin
            Route::post('create', 'create');       // Create new product
            Route::post('update/{id}', 'update');  // Update product
            Route::delete('delete/{id}', 'destroy');// Delete product
            Route::get('stats', 'stats');          // Get products stats (total, available, unavailable)
        });

    });

});
