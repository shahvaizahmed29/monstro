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

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('session/{session_id}', [App\Http\Controllers\Api\SessionController::class, 'getSessionCheckIns'])->name('get.session.checkins');
    Route::get('get-reservations-by-member', [App\Http\Controllers\Api\ReservationController::class, 'getReservationsByMember'])->name('get.reservations.by.member');
    Route::get('get-sessions/{program_id}', [App\Http\Controllers\Api\ProgramController::class, 'getSessions'])->name('get.sessions');
});


Route::post('login', [App\Http\Controllers\Api\AuthController::class, 'login'])->name('login');
