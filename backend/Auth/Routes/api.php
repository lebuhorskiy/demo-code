<?php

use App\Modules\Auth\Controllers\Login\EmailLoginController;
use App\Modules\Auth\Controllers\Register\EmailRegisterController;
use Illuminate\Support\Facades\Route;
use App\Modules\Auth\Controllers\Register\SocialRegisterController;
use App\Modules\Auth\Controllers\Register\Socials\TelegramAuthController;

Route::prefix('api/auth')->group(function () {
    Route::prefix('register')->group(function () {
        Route::post('/email', [EmailRegisterController::class, 'register']);
        Route::post('/verify-email', [EmailRegisterController::class, 'verify']);
    });

    Route::prefix('social')->group(function () {
        Route::get('/{provider}/start', [SocialRegisterController::class, 'start']);
        Route::get('/telegram/callback', [TelegramAuthController::class, 'callback']);
        Route::post('/{provider}/callback', [SocialRegisterController::class, 'callback']);
    });

    Route::prefix('login')->group(function () {
        Route::post('/email', [EmailLoginController::class, 'login']);
    });
});
