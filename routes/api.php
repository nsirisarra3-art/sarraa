<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\TripController;
use App\Http\Controllers\API\CustomerController;
use App\Http\Controllers\API\BookingController;

// Include the 'web' middleware so session-based auth works for the admin Blade SPA
Route::middleware(['api', 'web'])->group(function () {
    Route::apiResource('trips', TripController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('bookings', BookingController::class);
});
