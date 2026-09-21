<?php

use App\Http\Controllers\AgentAuthController;
use App\Http\Controllers\AgentPortalController;
use App\Http\Controllers\ReferralOperationsController;
use App\Http\Middleware\AuthenticateAgent;
use App\Http\Middleware\AuthenticateReferralStaff;
use App\Http\Middleware\ReferralPrivacy;
use Illuminate\Support\Facades\Route;

Route::middleware(ReferralPrivacy::class)->group(function () {
    Route::get('/refer/{code}', [AgentPortalController::class, 'publicForm'])->name('referral.public');
    Route::post('/refer/{code}', [AgentPortalController::class, 'publicStore'])->middleware('throttle:10,1')->name('referral.submit');
    Route::prefix('agent')->name('agent.')->group(function () {
        Route::get('/login', [AgentAuthController::class, 'login'])->name('login');
        Route::post('/login', [AgentAuthController::class, 'authenticate'])->middleware('throttle:20,1');
        Route::get('/forgot-password', [AgentAuthController::class, 'forgot'])->name('password.request');
        Route::post('/forgot-password', [AgentAuthController::class, 'email'])->middleware('throttle:5,1')->name('password.email');
        Route::get('/reset-password/{token}', [AgentAuthController::class, 'reset'])->name('password.reset');
        Route::post('/reset-password', [AgentAuthController::class, 'update'])->middleware('throttle:10,1')->name('password.update');
        Route::middleware(AuthenticateAgent::class)->group(function () {
            Route::get('/', [AgentPortalController::class, 'dashboard'])->name('dashboard');
            Route::post('/logout', [AgentAuthController::class, 'logout'])->name('logout');
            Route::get('/referrals/new', [AgentPortalController::class, 'create'])->name('create');
            Route::post('/referrals', [AgentPortalController::class, 'store'])->middleware('throttle:20,1')->name('store');
            Route::get('/referrals/{reference}', [AgentPortalController::class, 'show'])->name('show');
            Route::post('/referrals/{reference}/acknowledge', [AgentPortalController::class, 'acknowledge'])->name('acknowledge');
            Route::get('/statement', [AgentPortalController::class, 'statement'])->name('statement');
        });
    });
    Route::prefix('operations/referrals')->name('referral-ops.')->middleware(AuthenticateReferralStaff::class)->group(function () {
        Route::get('/', [ReferralOperationsController::class, 'index'])->name('index');
        Route::get('/agents', [ReferralOperationsController::class, 'agents'])->name('agents');
        Route::post('/agents', [ReferralOperationsController::class, 'saveAgent'])->name('agents.save');
        Route::post('/agents/{agent}/invite', [ReferralOperationsController::class, 'invite'])->middleware('throttle:10,1')->name('agents.invite');
        Route::get('/rules', [ReferralOperationsController::class, 'rules'])->name('rules');
        Route::post('/rules', [ReferralOperationsController::class, 'rule'])->name('rules.save');
        Route::post('/rules/{rule}/disable', [ReferralOperationsController::class, 'disableRule'])->name('rules.disable');
        Route::post('/agreements', [ReferralOperationsController::class, 'agreement'])->name('agreements.save');
        Route::get('/audit', [ReferralOperationsController::class, 'audit'])->name('audit');
        Route::get('/new', [ReferralOperationsController::class, 'manual'])->name('manual');
        Route::post('/new', [ReferralOperationsController::class, 'storeManual'])->name('manual.store');
        Route::get('/cases/{case}', [ReferralOperationsController::class, 'show'])->name('show');
        Route::post('/cases/{case}/progress', [ReferralOperationsController::class, 'progress'])->name('progress');
        Route::post('/cases/{case}/finance', [ReferralOperationsController::class, 'finance'])->name('finance');
    });
});
