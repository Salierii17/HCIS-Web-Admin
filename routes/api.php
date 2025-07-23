<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceRequestController;
use App\Http\Controllers\AuthenticationController;
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
        Route::apiResource('attendance', AttendanceController::class);
        Route::post('attendance-requests', [AttendanceRequestController::class, 'store']);

        Route::middleware('auth:api')->group(function () {
            
            // This route now requires authentication
            Route::apiResource('attendance', AttendanceController::class);
            
            // **FIX:** This route is now protected, so auth()->id() will work.
            Route::post('attendance-requests', [AttendanceRequestController::class, 'store']);
        
        });
    });
