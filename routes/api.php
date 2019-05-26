<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('/register','Auth\RegisterController@register')->name('register');
Route::post('/login','Auth\LoginController@login')->name('login');

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::prefix('cafe')->group(function() {
    Route::get('/create','CafeController@create')->name('cafeCreate');
    Route::get('/edit','CafeController@edit')->name('cafeEdit');
    Route::post('/store','CafeController@store')->name('cafeStore');
    Route::get('/join_requests','CafeController@allCafeJoinRequests')->name('allCafeJoinRequests');
    Route::get('/invite_requests','CafeController@allCafeInviteRequests')->name('allCafeInviteRequests');
    Route::get('/confirm_request','CafeController@confirmJoinRequest')->name('confirmJoinRequest');
    Route::get('/decline_request','CafeController@declineJoinRequest')->name('declineJoinRequest');
    Route::post('/invite','CafeController@inviteUser')->name('inviteStaff');
    
    Route::prefix('menu')->group(function() {
        Route::get('/create','CafeMenuController@create')->name('cafeMenuCreate');
        Route::get('/edit','CafeMenuController@edit')->name('cafeMenuEdit');
        Route::post('/store','CafeMenuController@store')->name('cafeMenuStore');
        Route::get('/','CafeMenuController@index')->name('cafeMenuIndex');
    });

    Route::prefix('category')->group(function() {
        Route::get('/create','CafeCategoryController@create')->name('cafeCategoryCreate');
        Route::get('/edit','CafeCategoryController@edit')->name('cafeCategoryEdit');
        Route::post('/store','CafeCategoryController@store')->name('cafeCategoryStore');
        Route::get('/','CafeCategoryController@index')->name('cafeCategoryIndex');
    });

    Route::prefix('member')->group(function() {
        Route::get('/check','CafeMemberController@checkIfUserIsAMember')->name('checkIfUserIsAMember');
        Route::get('/join','CafeMemberController@joinCafe')->name('joinCafe');
        Route::get('/make_admin','CafeMemberController@makeAdmin')->name('makeAdmin');
        Route::get('/remove_admin','CafeMemberController@removeAdmin')->name('removeAdmin');
        Route::get('/','CafeMemberController@CafeMembers')->name('cafeMemberIndex');
    });

    Route::prefix('wallet')->group(function() {
        Route::get('/','CafeWalletController@cafeWallet')->name('cafeWallet');
    });

    Route::prefix('custom_request')->group(function() {
        Route::get('/end_request','CafeCustomRequestController@endCafeCustomRequest')->name('endCafeCustomRequest');
        Route::get('/cancel_request','cafeCustomRequestController@cancelCafeCustomRequest')->name('cancelCafeCustomRequest');
        Route::get('/','CafeCustomRequestController@cafeCustomRequest')->name('cafeCustomRequestIndex');
    });

    Route::prefix('purchase')->group(function() {
        Route::get('/purchase_status','CafePurchaseController@changeCafePurchaseStatus')->name('changeCafePurchaseStatus');
        Route::get('/','CafePurchaseController@cafePendingPurchase')->name('cafePendingPurchase');
    });
    
    Route::prefix('item')->group(function() {
        Route::get('/create','CafeItemController@create')->name('cafeItemCreate');
        Route::get('/edit','CafeItemController@edit')->name('cafeItemEdit');
        Route::post('/store','CafeItemController@store')->name('cafeItemStore');
        Route::get('/','CafeItemController@index')->name('cafeItemIndex');
    });

    Route::get('/','CafeController@index')->name('cafeIndex');
});

Route::prefix('notification')->group(function() {
    Route::get('/seen','NotificationController@seenNotification')->name('seenNotification');
});

Route::prefix('user')->group(function() {
    Route::get('/verify','UserController@verifyUser')->name('verifyUser');
    Route::get('/verified','UserController@verifiedUser')->name('verifiedUser');
    Route::get('/profile','UserController@profile')->name('profile');
    Route::get('/pending_purchase','UserController@userPendingPurchase')->name('userPendingPurchase');
    Route::get('/create_custom_request','UserController@createCustomRequest')->name('createCustomRequest');
    Route::post('/store_custom_request','UserController@storeCustomRequest')->name('storeCustomRequest');
    Route::get('/cancel_join_request','UserController@cancelJoinRequest')->name('cancelJoinrequest');
    Route::get('/notification','UserController@userNotification')->name('userNotification');
    Route::get('/create_purchase','UserController@createUserPurchase')->name('createUserPurchase');
    Route::post('/single_store_purchase','UserController@userSingleStorePurchase')->name('userSingleStorePurchase');
    Route::get('/purchase_status','UserController@changeUserPurchaseStatus')->name('changeUserPurchaseStatus');
});

Route::get('/','IndexController@index')->name('index');
