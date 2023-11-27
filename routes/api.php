<?php

use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('get-reservations-by-member', [App\Http\Controllers\Api\ReservationController::class, 'getReservationsByMember'])->name('get.reservations.by.member');
    Route::get('get-reservations-by-id/{reservation_id}', [App\Http\Controllers\Api\ReservationController::class, 'getReservationById'])->name('get.reservations.by.id');
    Route::get('get-checkins/{reservation_id}', [App\Http\Controllers\Api\ReservationController::class, 'getCheckInsByReservation'])->name('get.checkins.by.reservation');
    Route::post('mark-attendance-check-in', [App\Http\Controllers\Api\ReservationController::class, 'markAttendance'])->name('mark.attendance');
    Route::put('mark-checkout/{check_in}', [App\Http\Controllers\Api\ReservationController::class, 'markCheckOut'])->name('mark.checkout');
// });

Route::post('login', [App\Http\Controllers\Api\AuthController::class, 'login'])->name('login');
