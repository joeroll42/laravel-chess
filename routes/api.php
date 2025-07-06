<?php

use App\Http\Controllers\Api\ChallengeController;
use App\Http\Controllers\Api\PlatformController;
use App\Http\Controllers\Api\RolesController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Auth\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::post('/tokens/create',[ApiController::class, 'gen_access_token']);

Route::post('/users', [UserController::class, 'store']); // Public: no auth

Route::middleware('auth:sanctum')->group(function () {
    Route::resource('users', UserController::class)->except(['store']);
});



Route::resource('/roles', RolesController::class)->middleware('auth:sanctum');
Route::resource('/platforms', PlatformController::class)->middleware('auth:sanctum');
Route::resource('/challenges', ChallengeController::class)->middleware('auth:sanctum');

Route::get('/user',[UserController::class, 'activeUser'])
    ->name('active-user')
    ->middleware('auth:sanctum');


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/users/online', function (Request $request) {
        $user = $request->user(); // ✅ use logged-in user, not payload
        $user->is_online = true;
        $user->last_seen_at = now();
        $user->save();

        return response()->json(['status' => 'updated']);
    });

    Route::post('/users/offline', function (Request $request) {
        $user = $request->user();
        $user->is_online = false;
        $user->last_seen_at = now();
        $user->save();

        return response()->json(['status' => 'updated']);
    });
});

//Route::middleware('auth:sanctum')->prefix('challenges')->group(function () {
//    Route::apiResource('/', ChallengeController::class);
//});
