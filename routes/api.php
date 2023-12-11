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
    //==================================================================================================================================================================================
    //===================================================================================== Members Routes =============================================================================
    //==================================================================================================================================================================================
    Route::group(['prefix' => 'member', 'middleware' => ['is_member']], function () {
        Route::get('get-reservations-by-member', [App\Http\Controllers\Api\Member\ReservationController::class, 'getReservationsByMember'])->name('get.reservations.by.member');
        Route::get('get-reservations-by-id/{reservation_id}', [App\Http\Controllers\Api\Member\ReservationController::class, 'getReservationById'])->name('get.reservations.by.id');
        Route::get('get-checkins/{reservation_id}', [App\Http\Controllers\Api\Member\ReservationController::class, 'getCheckInsByReservation'])->name('get.checkins.by.reservation');
        Route::post('mark-attendance', [App\Http\Controllers\Api\Member\ReservationController::class, 'markAttendance'])->name('mark.attendance');
    });

    //==================================================================================================================================================================================
    //===================================================================================== Vendors Routes =============================================================================
    //==================================================================================================================================================================================
    Route::group(['prefix' => 'vendor', 'middleware' => ['is_vendor']], function () {
        Route::post('authenticate', [App\Http\Controllers\Api\Vendor\AuthController::class, 'vendorAuthenticate'])->name('vendor.authenticate');
    });

    Route::get('logout', [App\Http\Controllers\Api\Vendor\AuthController::class, 'logout'])->name('logout');
});

//==================================================================================================================================================================================
//===================================================================================== Vendors Public Routes =============================================================================
//==================================================================================================================================================================================
Route::group(['prefix' => 'vendor', 'middleware' => ['checkLocationId']],function () {
    Route::get('get-vendor-locations', [App\Http\Controllers\Api\Vendor\LocationsController::class, 'getVendorLocations'])->name('get.vendor.locations');
    Route::get('get-programs-by-location', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'getProgramsByLocation'])->name('get.programs.by.location');
    Route::get('get-programs-by-id/{id}', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'getProgramById'])->name('get.program.by.id');
    Route::post('add-program', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'addProgram'])->name('add.program');
    Route::get('get-members-by-location', [App\Http\Controllers\Api\Vendor\MemberController::class, 'getMembersByLocation'])->name('get.members.by.location');
    Route::get('get-reservations-by-member/{member_id}', [App\Http\Controllers\Api\Vendor\ReservationController::class, 'getReservationsByMember'])->name('get.reservations.by.member');
    Route::post('mark-attendance', [App\Http\Controllers\Api\Vendor\ReservationController::class, 'markAttendance'])->name('mark.attendance');
    Route::get('member-details/{member_id}', [App\Http\Controllers\Api\Vendor\MemberController::class, 'getMemberDetails'])->name('get.member.details');
    Route::get('get-checkins/{reservation_id}', [App\Http\Controllers\Api\Vendor\ReservationController::class, 'getCheckInsByReservation'])->name('get.checkins.by.reservation');
});

Route::get('sync-members-by-location/{locationId}', [App\Http\Controllers\Api\Vendor\PublicController::class, 'syncMembersByLocation'])->name('sync.member.by.location');

Route::post('login', [App\Http\Controllers\Api\AuthController::class, 'login'])->name('login');
