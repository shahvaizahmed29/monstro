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

Route::get('get-reservations-by-member', [App\Http\Controllers\ReservationController::class, 'getReservationsByMember'])->name('get.reservations');
Route::get('get-sessions/{program_id}', [App\Http\Controllers\ProgramController::class, 'getSessions'])->name('get.sessions');
