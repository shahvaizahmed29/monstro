<?php

use App\Http\Controllers\Api\Vendor\RoleAndPermissionController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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

Route::group(['prefix' => 'member'], function () {
    Route::post('register', [App\Http\Controllers\Api\Member\MemberController::class, 'register'])->name('register.member');
});

Route::post('password/forgot', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('password/reset', [ResetPasswordController::class, 'reset']);

Route::middleware(['auth:sanctum'])->group(function () {
    //==================================================================================================================================================================================
    //===================================================================================== Members Routes =============================================================================
    //==================================================================================================================================================================================
    Route::group(['prefix' => 'member', 'middleware' => ['is_member']], function () {
        Route::get('get-login-member', [App\Http\Controllers\Api\Member\MemberController::class, 'getLoginMember'])->name('get.login.member');
        Route::get('get-reservations-by-member', [App\Http\Controllers\Api\Member\ReservationController::class, 'getReservationsByMember'])->name('get.reservations.by.member');
        Route::get('get-reservations-by-id/{reservation_id}', [App\Http\Controllers\Api\Member\ReservationController::class, 'getReservationById'])->name('get.reservations.by.id');
        Route::get('get-checkins/{reservation_id}', [App\Http\Controllers\Api\Member\ReservationController::class, 'getCheckInsByReservation'])->name('get.checkins.by.reservation');
        Route::post('mark-attendance', [App\Http\Controllers\Api\Member\ReservationController::class, 'markAttendance'])->name('mark.attendance');
        Route::put('profile-update/{user_id}', [App\Http\Controllers\Api\Member\MemberController::class, 'profileUpdate'])->name('profile.update');
        Route::put('update-password/{user_id}', [App\Http\Controllers\Api\Member\MemberController::class, 'updatePassword'])->name('update.password');
        Route::get('profile', [App\Http\Controllers\Api\Member\MemberController::class, 'getProfile'])->name('get.profile');
        Route::get('rewards', [App\Http\Controllers\Api\Member\MemberController::class, 'getMemberRewards'])->name('get.member.rewards');
        Route::get('tradable-rewards', [App\Http\Controllers\Api\Member\MemberController::class, 'getTradableRewards'])->name('get.member.tradable.rewards');
        Route::get('achievement-rewards', [App\Http\Controllers\Api\Member\MemberController::class, 'getAchievementRewards'])->name('get.member.achievement.rewards');
        Route::get('unclaimed-achievements', [App\Http\Controllers\Api\Member\MemberController::class, 'getUnclaimedAchievements'])->name('get.member.unclaimed.achievements');
        Route::get('claimed-achievements', [App\Http\Controllers\Api\Member\MemberController::class, 'getClaimedAchievements'])->name('get.member.claimed.achievements');
        Route::post('claim-reward-tradeable', [App\Http\Controllers\Api\Member\MemberController::class, 'claimRewardTradeable'])->name('member.claim.reward.tradeable');
        Route::post('claim-reward-achieveable', [App\Http\Controllers\Api\Member\MemberController::class, 'claimRewardAchieveable'])->name('member.claim.reward.achieveable');
        Route::get('achievements', [App\Http\Controllers\Api\Member\MemberController::class, 'getMemberAchievements'])->name('get.member.achievements');
        Route::post('redeem-points', [App\Http\Controllers\Api\Member\MemberController::class, 'redeemPoints'])->name('redeem.points');
        Route::get('current-points', [App\Http\Controllers\Api\Member\MemberController::class, 'getCurrentPoints'])->name('get.current.points');
        Route::get('get-programs-by-locations', [App\Http\Controllers\Api\Member\MemberController::class, 'getProgramByLocations'])->name('get.programs.by.location');
        Route::post('enroll-in-program/{programId}', [App\Http\Controllers\Api\Member\MemberController::class, 'enrollInProgram'])->name('enroll.in.program');
        Route::get('enrolled-programs/{locationId}', [App\Http\Controllers\Api\Member\MemberController::class, 'getMemberEnrolledPrograms'])->name('get.member.enrolled.programs');
        Route::get('active-vendors', [App\Http\Controllers\Api\Member\MemberController::class, 'getMemberActiveVendors'])->name('get.member.active.vendors');
        Route::get('active-locations', [App\Http\Controllers\Api\Member\MemberController::class, 'getMemberActiveLocations'])->name('get.member.active.locations');
        Route::get('upcoming-classes', [App\Http\Controllers\Api\Member\ReservationController::class, 'getReservationsByMember'])->name('get.member.upcoming.classes');

        // routes for enroll in program.
        Route::get('programs-with-plans-by-location/{locationId}', [App\Http\Controllers\Api\Member\MemberController::class, 'getProgramsWithPlans'])->name('get.programs.with.plans');
        Route::get('get-plan-contract/{planId}', [App\Http\Controllers\Api\Member\MemberController::class, 'getPlanContract'])->name('get.plan.contract');
        Route::get('get-plan/{planId}', [App\Http\Controllers\Api\Member\MemberController::class, 'getPlan'])->name('get.plan');
        Route::get('contract/{contractId}/variables', [App\Http\Controllers\Api\Vendor\ContractController::class, 'getContractVariables'])->name('contract.variables.fetch');
        Route::post('fill-contract', [App\Http\Controllers\Api\Vendor\ContractController::class, 'fillContract'])->name('fill.contract');
        Route::get('fetch-vendor-stripe-pk/{programId}', [App\Http\Controllers\Api\Member\MemberController::class, 'fetchVendorStripePk'])->name('fetch.vendor.stripe.pk');
        Route::post('program/plan/subscribe/{programId}/{planId}', [App\Http\Controllers\Api\PaymentController::class, 'completeSubscription'])->name('complete.subscription');
    });

    //==================================================================================================================================================================================
    //===================================================================================== Vendors Routes =============================================================================
    //==================================================================================================================================================================================
    Route::group(['prefix' => 'vendor', 'middleware' => ['is_vendor']], function () {
        Route::post('authenticate', [App\Http\Controllers\Api\Vendor\AuthController::class, 'vendorAuthenticate'])->name('vendor.authenticate');
        Route::get('profile', [App\Http\Controllers\Api\Vendor\VendorController::class, 'getProfile'])->name('get.profile');
        Route::put('update-profile', [App\Http\Controllers\Api\Vendor\VendorController::class, 'updateProfile'])->name('update.profile');
    });
    
    Route::get('logout', [App\Http\Controllers\Api\Vendor\AuthController::class, 'logout'])->name('logout');

    Route::group(['prefix' => 'vendor', 'middleware' => ['checkLocationId', 'is_vendor']],function () {
        Log::info(Auth::check());
        Log::info(request()->bearerToken());
        Log::info(Auth::user());
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
        Route::get('get-contacts', [App\Http\Controllers\Api\Vendor\MemberController::class, 'getContacts'])->name('get.contacts');
        Route::post('add-member', [App\Http\Controllers\Api\Vendor\MemberController::class, 'addMemberManually'])->name('add.member.manually');
        Route::put('member/status/{member_id}', [App\Http\Controllers\Api\Vendor\MemberController::class, 'memberStatusUpdate'])->name('member.status.update');
        Route::get('attendances/member/{memberId}/program/{programId}', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'lastTenAttendance'])->name('member.program.attendances');
        Route::get('member/{memberId}/password-reset', [App\Http\Controllers\Api\Vendor\MemberController::class, 'memberPasswordReset'])->name('member.password.reset');
        Route::get('member/{memberId}/get-family', [App\Http\Controllers\Api\Vendor\MemberController::class, 'getFamilyMembers'])->name('member.fetch.family');
        Route::post('member/{memberId}/add-family-members', [App\Http\Controllers\Api\Vendor\MemberController::class, 'addFamilyMembers'])->name('member.add.family.members');
        
        Route::prefix('achievements')->group(function () {
            Route::get('', [App\Http\Controllers\Api\Vendor\AchievementController::class, 'index'])->name('achievement.all');
            Route::post('', [App\Http\Controllers\Api\Vendor\AchievementController::class, 'create'])->name('create.achievement');
            Route::get('{achievementId}', [App\Http\Controllers\Api\Vendor\AchievementController::class, 'getAchievement'])->name('get.achievement');
            Route::put('{achievement}', [App\Http\Controllers\Api\Vendor\AchievementController::class, 'update'])->name('update.achievement');
            Route::delete('{achievementId}', [App\Http\Controllers\Api\Vendor\AchievementController::class, 'delete'])->name('delete.achievement');
        });
        Route::post('rewards/{rewardId}/update', [App\Http\Controllers\Api\Vendor\RewardController::class, 'update'])->name('update.reward');
        
        Route::prefix('location')->group(function () {
            Route::get('', [App\Http\Controllers\Api\Vendor\LocationsController::class, 'checkLocationStatus'])->name('check.location.status');
            Route::get('{locationId}', [App\Http\Controllers\Api\Vendor\LocationsController::class, 'getLocatonById'])->name('get.location.by.id');
            Route::put('{locationId}', [App\Http\Controllers\Api\Vendor\LocationsController::class, 'updateLocation'])->name('update.location');
        });
        
        Route::prefix('actions')->group(function () {
            Route::get('', [App\Http\Controllers\Api\Vendor\ActionController::class, 'index'])->name('get.actions');
        });
        
        Route::prefix('rewards')->group(function () {
            // Route::get('', [App\Http\Controllers\Api\Vendor\RewardController::class, 'index'])->name('get.rewards');
            Route::get('member/{memberId}', [App\Http\Controllers\Api\Vendor\RewardController::class, 'getMemberRewards'])->name('get.member.rewards');
            // Route::put('{id}', [App\Http\Controllers\Api\Vendor\RewardController::class, 'restore'])->name('restore.reward');
            // Route::delete('{id}', [App\Http\Controllers\Api\Vendor\RewardController::class, 'delete'])->name('delete.reward');
            // Route::post('', [App\Http\Controllers\Api\Vendor\RewardController::class, 'store'])->name('create.reward');
        });
        Route::apiResource('rewards', App\Http\Controllers\Api\Vendor\RewardController::class);
        
        Route::put('password-reset', [App\Http\Controllers\Api\Vendor\VendorController::class, 'passwordReset'])->name('password.reset');        
        Route::post('assign-program-level/{programLevelId}/member/{memberId}', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'assignProgramLevelToMember'])->name('assign.program.level.to.member');
        Route::post('assign-program/{programId}/member/{memberId}', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'assignProgramToMember'])->name('assign.program.to.member');
        Route::get('unassign-program/{programId}/member/{memberId}', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'unAssignMemberFromProgram'])->name('unassign.member.from.program');
        Route::get('complete-program/{programId}/member/{memberId}', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'programCompletion'])->name('program.completion.for.member');
        
        Route::put('member/{memberId}/profile-update', [App\Http\Controllers\Api\Vendor\MemberController::class, 'profileUpdate'])->name('member.profile.update');
        Route::get('member/{memberId}/get-programs', [App\Http\Controllers\Api\Vendor\MemberController::class, 'getMemberPrograms'])->name('get.member.programs');
        Route::get('member/{memberId}/get-programs-not-enrolled', [App\Http\Controllers\Api\Vendor\MemberController::class, 'getProgramsForMemberNotEnrolled'])->name('get.member.programs.NotEnrolled');
        Route::get('program-level/program/{programId}', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'getProgramLevels'])->name('get.program.levels');
        Route::post('member/create', [App\Http\Controllers\Api\Vendor\MemberController::class, 'createMember'])->name('create.member');
        Route::get('program/{programId}/get-archive-program-levels', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'getArchiveProgramLevelsWithSession'])->name('get.archive.program.levels');
        Route::post('program/{program}/image', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'programImageUpdate'])->name('program.image.update');
        
        Route::get('stripe-plans/{programId}', [App\Http\Controllers\Api\Vendor\StripePlanController::class, 'getPlans'])->name('stripe.plans.fetch');
        Route::get('stripe-plans/single/{planId}', [App\Http\Controllers\Api\Vendor\StripePlanController::class, 'getPlan'])->name('stripe.plan.fetch');
        Route::post('create-contract', [App\Http\Controllers\Api\Vendor\ContractController::class, 'addContract'])->name('contract.create');
        Route::get('contracts', [App\Http\Controllers\Api\Vendor\ContractController::class, 'getContracts'])->name('get.contracts');
        Route::get('contracts/signed', [App\Http\Controllers\Api\Vendor\ContractController::class, 'getSignedContracts'])->name('get.signed.contracts');
        Route::get('contracts/signed/{contractId}', [App\Http\Controllers\Api\Vendor\ContractController::class, 'createPdf'])->name('get.signed.contract.pdf');
        Route::get('contracts/program/{programId}', [App\Http\Controllers\Api\Vendor\ContractController::class, 'getContractsByProgram'])->name('contracts.fetch');
        Route::post('contracts/{contractId}', [App\Http\Controllers\Api\Vendor\ContractController::class, 'updateContractById'])->name('update.contract');
        Route::get('contracts/{contractId}', [App\Http\Controllers\Api\Vendor\ContractController::class, 'getContractById'])->name('contract.single.fetch');
        Route::delete('contracts/{contractId}', [App\Http\Controllers\Api\Vendor\ContractController::class, 'deleteContract'])->name('contract.single.delete');
        Route::post('invite-member', [App\Http\Controllers\Api\Vendor\MemberController::class, 'inviteMember'])->name('invite.member');
        Route::post('add-member-plan/{programId}', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'addPlan'])->name('plan.create');
        Route::put('update-member-plan/{programId}/{plan}', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'updatePlan'])->name('plan.create');
        Route::post('update-location-info', [App\Http\Controllers\Api\Vendor\VendorController::class, 'updateLocation'])->name('vendor.location.update');
        Route::post('integrations/{service}', [App\Http\Controllers\Api\Vendor\IntegrationController::class, 'completeConnection'])->name('complete.integration.connection');
        Route::get('integrations', [App\Http\Controllers\Api\Vendor\IntegrationController::class, 'getIntegrations'])->name('fetch.integrations');
        Route::delete('integrations/{id}', [App\Http\Controllers\Api\Vendor\IntegrationController::class, 'delIntegrations'])->name('delete.integrations');
        Route::get('fetch-vendor-stripe-pk', [App\Http\Controllers\Api\Vendor\VendorController::class, 'fetchVendorStripePk'])->name('fetch.vendor.stripe.pk');
        Route::apiResource('roles', App\Http\Controllers\Api\Vendor\RoleAndPermissionController::class)->except(['index', 'show']);
        Route::apiResource('staffs', App\Http\Controllers\Api\Vendor\StaffController::class)->except(['index', 'show']);
    });

    Route::group(['prefix' => 'vendor', 'middleware' => ['checkLocationId', 'staff_or_vendor']],function () { 

        Route::put('update-password',[App\Http\Controllers\Api\Vendor\VendorController::class, 'vendorUpdatePassword'])->name('vendor.update.password');

        //edit contract
        Route::post('contracts/{contractId}', [App\Http\Controllers\Api\Vendor\ContractController::class, 'updateContractById'])->middleware('permission:edit contract')->name('update.contract.for.staff');
        
        //add contract
        Route::post('create-contract', [App\Http\Controllers\Api\Vendor\ContractController::class, 'addContract'])->middleware('permission:add contract')->name('contract.create.for.staff');
        
        //delete contract
        Route::delete('contracts/{contractId}', [App\Http\Controllers\Api\Vendor\ContractController::class, 'deleteContract'])->middleware('permission:delete contract')->name('contract.single.delete.for.staff');
        
        //edit role
        Route::put('roles/{id}', [App\Http\Controllers\Api\Vendor\RoleAndPermissionController::class, 'update'])->middleware('permission:edit role')->name('role.update.for.staff');
        //add role
        Route::put('roles', [App\Http\Controllers\Api\Vendor\RoleAndPermissionController::class, 'store'])->middleware('permission:add role')->name('role.add.for.staff');
        //delete role
        Route::put('roles/{id}', [App\Http\Controllers\Api\Vendor\RoleAndPermissionController::class, 'destroy'])->middleware('permission:delete role')->name('role.delete.for.staff');
        
        Route::group(['middleware' => ['permission:edit business_profile']],function () {
            //edit business_profile
            Route::put('location/{locationId}', [App\Http\Controllers\Api\Vendor\LocationsController::class, 'updateLocation'])->name('update.location.for.staff');
            Route::post('update-location-info', [App\Http\Controllers\Api\Vendor\VendorController::class, 'updateLocation'])->name('vendor.location.update.for.staff');
            Route::post('integrations/{service}', [App\Http\Controllers\Api\Vendor\IntegrationController::class, 'completeConnection'])->name('complete.integration.connection.for.staff');
            Route::delete('integrations/{id}', [App\Http\Controllers\Api\Vendor\IntegrationController::class, 'delIntegrations'])->name('delete.integrations.for.staff');
        });
    
        Route::group(['middleware' => ['permission:edit member']],function () {
            //edit member
            Route::get('member/{memberId}/password-reset', [App\Http\Controllers\Api\Vendor\MemberController::class, 'memberPasswordReset'])->name('member.password.reset.for.staff');
            Route::post('assign-program-level/{programLevelId}/member/{memberId}', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'assignProgramLevelToMember'])->name('assign.program.level.to.member.for.staff');
            Route::post('assign-program/{programId}/member/{memberId}', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'assignProgramToMember'])->name('assign.program.to.member.for.staff');
            Route::get('unassign-program/{programId}/member/{memberId}', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'unAssignMemberFromProgram'])->name('unassign.member.from.program.for.staff');
            Route::get('complete-program/{programId}/member/{memberId}', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'programCompletion'])->name('program.completion.for.member.for.staff');
            Route::post('mark-attendance', [App\Http\Controllers\Api\Vendor\ReservationController::class, 'markAttendance'])->name('mark.attendance.for.staff');
            Route::put('member/status/{member_id}', [App\Http\Controllers\Api\Vendor\MemberController::class, 'memberStatusUpdate'])->name('member.status.update.for.staff');
            Route::put('member/{memberId}/profile-update', [App\Http\Controllers\Api\Vendor\MemberController::class, 'profileUpdate'])->name('member.profile.update.for.staff');
        });
        
        Route::group(['middleware' => ['permission:add member']],function () {
            //add member
            Route::post('add-member', [App\Http\Controllers\Api\Vendor\MemberController::class, 'addMemberManually'])->middleware('permission:add member')->name('add.member.manually.for.staff');
            Route::post('invite-member', [App\Http\Controllers\Api\Vendor\MemberController::class, 'inviteMember'])->name('invite.member.for.staff');
            Route::post('member/create', [App\Http\Controllers\Api\Vendor\MemberController::class, 'createMember'])->name('create.member.for.staff');
        
        });
    
        Route::group(['middleware' => ['permission:edit achievement']],function () {
            //edit achievement
            
            Route::put('achievements/{achievement}', [App\Http\Controllers\Api\Vendor\AchievementController::class, 'update'])->name('update.achievement.for.staff');
            Route::post('rewards/{rewardId}/update', [App\Http\Controllers\Api\Vendor\RewardController::class, 'update'])->name('update.reward.for.staff');
        });
    
        Route::group(['middleware' => ['permission:add achievement']],function () {
            //add achievement
            Route::post('achievements', [App\Http\Controllers\Api\Vendor\AchievementController::class, 'create'])->name('create.achievement.for.staff');
            Route::post('rewards', [App\Http\Controllers\Api\Vendor\RewardController::class, 'store'])->name('create.reward.for.staff');
        });
    
        Route::group(['middleware' => ['permission:delete achievement']],function () {
            //delete achievement
            Route::delete('achievements/{achievementId}', [App\Http\Controllers\Api\Vendor\AchievementController::class, 'delete'])->name('delete.achievement.for.staff');
            Route::put('rewards/{id}', [App\Http\Controllers\Api\Vendor\RewardController::class, 'restore'])->name('restore.reward.for.staff');
            Route::delete('rewards/{id}', [App\Http\Controllers\Api\Vendor\RewardController::class, 'delete'])->name('delete.reward.for.staff');    
        });
    
        //delete member
        // no function to delete a member
        
        Route::group(['middleware' => ['permission:edit program']],function () {
            //edit program
            Route::post('/program/{program}', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'update'])->name('program.update.for.staff');
            Route::put('/program-level-update/{programLevel}', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'programLevelUpdate'])->name('program.level.update.for.staff');
            Route::put('update-member-plan/{programId}/{plan}', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'updatePlan'])->name('plan.create');
        });
        
        Route::group(['middleware' => ['permission:add program']],function () {
            //add program
            Route::post('add-program', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'addProgram'])->name('add.program.for.staff');
            Route::post('add-program-level', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'addProgramLevel'])->name('add.program.level.for.staff');
            Route::post('program/{program}/image', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'programImageUpdate'])->name('program.image.update.for.staff');
            Route::post('add-member-plan/{programId}', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'addPlan'])->name('plan.create.for.staff');
        });
        
        Route::group(['middleware' => ['permission:delete program']],function () {
            //delete program
            Route::delete('program/{program_id}', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'delete'])->name('delete.program.for.staff');
            Route::delete('program-level/{programLevelId}', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'deleteProgramLevel'])->name('delete.program.level.for.staff');
        });

        Route::group(['prefix' => 'transactions/{model}'],function () {
            Route::post('', [App\Http\Controllers\Api\PaymentController::class, 'addManualPayment'])->name('manual.payment.create');
        });
    
    });
});

//==================================================================================================================================================================================
//===================================================================================== Vendors Public Routes =============================================================================
//==================================================================================================================================================================================

Route::group(['prefix' => 'vendor/register'],function () {
    Route::post('44a1a08a-3109-4199-ad64-fa484ed6b656/gmnq69ju9hds/b0be1897-4db1-4cfb-9c0f-38321a7e6fcb', [App\Http\Controllers\Api\Vendor\VendorController::class, 'registerVendor'])->name('vendor.register.admin');
    Route::get('get-programs-by-vendor/{vendorId}', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'getProgramsByVendor'])->name('get.programs.by.Vendor');
    Route::get('stripe-plans/{programId}', [App\Http\Controllers\Api\Vendor\StripePlanController::class, 'getPlans'])->name('regitser.stripe.plans.fetch');
    Route::get('get-programs-by-location/{locationId}', [App\Http\Controllers\Api\Vendor\ProgramController::class, 'getProgramsByLocationId'])->name('get.programs.by.location');
});

Route::post('image-update/{user_id}', [App\Http\Controllers\PublicController::class, 'imageUpdate'])->name('image.update');
Route::post('member-image-update/{memberId}', [App\Http\Controllers\PublicController::class, 'memberImageUpdate'])->name('member.image.update');

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
