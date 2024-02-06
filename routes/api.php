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
        Route::put('profile-update/{user_id}', [App\Http\Controllers\Api\Member\MemberController::class, 'profileUpdate'])->name('profile.update');
        Route::put('update-password/{user_id}', [App\Http\Controllers\Api\Member\MemberController::class, 'updatePassword'])->name('update.password');
        Route::get('profile', [App\Http\Controllers\Api\Member\MemberController::class, 'getProfile'])->name('get.profile');
    });

    //==================================================================================================================================================================================
    //===================================================================================== Vendors Routes =============================================================================
    //==================================================================================================================================================================================
    Route::group(['prefix' => 'vendor', 'middleware' => ['is_vendor']], function () {
        Route::post('authenticate', [App\Http\Controllers\Api\Vendor\AuthController::class, 'vendorAuthenticate'])->name('vendor.authenticate');
        Route::get('profile', [App\Http\Controllers\Api\Vendor\VendorController::class, 'getProfile'])->name('get.profile');
        Route::put('update-password/{user_id}',[App\Http\Controllers\Api\Vendor\VendorController::class, 'vendorUpdatePassword'])->name('vendor.update.password');
        Route::put('update-profile', [App\Http\Controllers\Api\Vendor\VendorController::class, 'updateProfile'])->name('update.profile');
    });

    Route::get('logout', [App\Http\Controllers\Api\Vendor\AuthController::class, 'logout'])->name('logout');
});

//==================================================================================================================================================================================
//===================================================================================== Vendors Public Routes =============================================================================
//==================================================================================================================================================================================
Route::group(['prefix' => 'vendor', 'middleware' => ['checkLocationId']],function () {
    Route::get('get-programs-by-location', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'getProgramsByLocation'])->name('get.programs.by.location');
    Route::get('get-programs-by-id/{id}', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'getProgramById'])->name('get.program.by.id');
    Route::post('add-program', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'addProgram'])->name('add.program');
    Route::post('add-program-level', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'addProgramLevel'])->name('add.program.level');
    Route::get('get-members-by-location', [App\Http\Controllers\Api\Vendor\MemberController::class, 'getMembersByLocation'])->name('get.members.by.location');
    Route::get('get-members-by-program/{program_id}', [App\Http\Controllers\Api\Vendor\MemberController::class, 'getMembersByProgram'])->name('get.members.by.program');
    Route::get('get-reservations-by-member/{member_id}', [App\Http\Controllers\Api\Vendor\ReservationController::class, 'getReservationsByMember'])->name('get.reservations.by.member');
    Route::post('mark-attendance', [App\Http\Controllers\Api\Vendor\ReservationController::class, 'markAttendance'])->name('mark.attendance');
    Route::get('member-details/{member_id}', [App\Http\Controllers\Api\Vendor\MemberController::class, 'getMemberDetails'])->name('get.member.details');
    Route::get('get-checkins/{reservation_id}', [App\Http\Controllers\Api\Vendor\ReservationController::class, 'getCheckInsByReservation'])->name('get.checkins.by.reservation');
    Route::get('program-details/{program_id}', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'getProgramDetails'])->name('get.program.details');
    Route::get('get-session-meetings-by-member/{member_id}', [App\Http\Controllers\Api\Vendor\ReservationController::class, 'memberUpcomingMeetings'])->name('get.session.meetings.by.member');
    Route::get('program-level-meetings/{program_level_id}', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'programLevelActiveSessions'])->name('program.level.active.sessions');
    Route::post('/program/{program}', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'update'])->name('program.update');
    Route::put('/program-level-update/{programLevel}', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'programLevelUpdate'])->name('program.level.update');
    Route::delete('program/{program_id}', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'delete'])->name('delete.program');
    Route::delete('program-level/{programLevelId}', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'deleteProgramLevel'])->name('delete.program.level');
    Route::get('sync-members-by-location/{programId}', [App\Http\Controllers\Api\Vendor\MemberController::class, 'syncMembersByLocation'])->name('sync.member.by.location');
    Route::get('get-contacts', [App\Http\Controllers\Api\Vendor\MemberController::class, 'getContacts'])->name('get.contacts');
    Route::post('add-member/{programLevelId}', [App\Http\Controllers\Api\Vendor\MemberController::class, 'addMemberManually'])->name('add.member.manually');
    Route::put('/program-level-archive/{programLevelId}', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'programLevelArchive'])->name('program.level.archive');
    Route::put('member/status/{member_id}', [App\Http\Controllers\Api\Vendor\MemberController::class, 'memberStatusUpdate'])->name('member.status.update');
    Route::get('attendances/member/{memberId}/program/{programId}', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'lastTenAttendance'])->name('member.program.attendances');
});

Route::post('image-update/{user_id}', [App\Http\Controllers\PublicController::class, 'imageUpdate'])->name('image.update');

Route::get('vendor/{vendor_id}/locations' , [\App\Http\Controllers\Api\Vendor\LocationsController::class , 'vendorLocations'])->name('vendor.locations');
Route::put('vendor/{vendor_id}/update-passowrd',[App\Http\Controllers\Api\Vendor\VendorController::class, 'updatePassword'])->name('update.passowrd');
Route::get('/support', [App\Http\Controllers\Api\SupportCategoryController::class, 'index'])->name('support.categories');
Route::post('support/ticket/create', [App\Http\Controllers\Api\SupportCategoryController::class, 'createSupportTicket'])->name('create.support.ticket');
Route::get('/plans', [App\Http\Controllers\Api\PlansController::class, 'getPlans'])->name('get.plans');
Route::get('/plans/{name}', [App\Http\Controllers\Api\PlansController::class, 'getPlansByName'])->name('get.plans.by.name');
Route::get('/steps', [App\Http\Controllers\Api\StepsController::class, 'getSteps'])->name('get.steps');
Route::get('/steps/{step_id}', [App\Http\Controllers\Api\StepsController::class, 'getSingleStep'])->name('get.single.steps');

//==================================================================================================================================================================================
//===================================================================================== Payment Routes Below =============================================================================
//==================================================================================================================================================================================
Route::post('payments/deposit', [App\Http\Controllers\Api\PaymentController::class, 'deposit'])->name('payment.deposit');
Route::post('payments/subscribe', [App\Http\Controllers\Api\PaymentController::class, 'subscribe'])->name('payment.subscribe');


Route::post('login', [App\Http\Controllers\Api\AuthController::class, 'login'])->name('login');
