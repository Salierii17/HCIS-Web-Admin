<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceRequestController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('api')
    ->prefix('v1')
    ->group(function () {
        /*
         * Authentication Route
         */
        Route::prefix('auth')
            ->group(function () {
                Route::post('login', [AuthenticationController::class, 'login']);
                Route::post('logout', [AuthenticationController::class, 'logout'])
                    ->middleware('auth:api');
                Route::post('refresh', [AuthenticationController::class, 'refresh'])
                    ->middleware('auth:api');
            });
        // Route::middleware('auth:api')->group(function () {
        //     Route::post('attendance', [AttendanceController::class, 'store']);
        // });
        // Route::apiResource('attendance', AttendanceController::class);
        // Route::post('attendance-requests', [AttendanceRequestController::class, 'store']);

        Route::middleware('auth:api')->group(function () {

            // This route now requires authentication
            Route::apiResource('attendance', AttendanceController::class);
            Route::post('attendance-requests', [AttendanceRequestController::class, 'store']);

            Route::prefix('profile')->group(function () {
                Route::get('/', [ProfileController::class, 'show']);
                Route::put('/', [ProfileController::class, 'update']);
                Route::post('photo', [ProfileController::class, 'uploadPhoto']);
                Route::delete('photo', [ProfileController::class, 'deletePhoto']);
                Route::put('password', [ProfileController::class, 'changePassword']);
                Route::delete('account', [ProfileController::class, 'deleteAccount']);
            });

            // Notification Routes
            Route::prefix('notifications')->group(function () {
                Route::get('/', [NotificationController::class, 'index']);
                Route::post('{notificationId}/read', [NotificationController::class, 'markAsRead']);
            });

        });
    });
