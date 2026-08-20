<?php

use App\Http\Controllers\CompareController;
use App\Http\Controllers\CostEstimatorController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\TrackController;
use Illuminate\Support\Facades\Route;

Route::get('/language/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['bn', 'en'], true), 404);

    session(['locale' => $locale]);
    app()->setLocale($locale);

    return back();
})->name('language.switch');

Route::get('/', [PageController::class, 'home'])->name('home');

Route::get('/doctors', [PageController::class, 'doctors'])->name('doctors.index');
Route::get('/doctors/{doctor}', [PageController::class, 'doctorShow'])->name('doctors.show');

Route::get('/hospitals', [PageController::class, 'hospitals'])->name('hospitals.index');
Route::get('/hospitals/{hospital}', [PageController::class, 'hospitalShow'])->name('hospitals.show');

Route::get('/treatments', [PageController::class, 'treatments'])->name('treatments.index');
Route::get('/treatments/{treatment}', [PageController::class, 'treatmentShow'])->name('treatments.show');

Route::get('/health-packages', [PageController::class, 'healthPackages'])->name('health-packages.index');
Route::get('/services', [PageController::class, 'services'])->name('services.index');
Route::get('/services/{service}', [PageController::class, 'serviceShow'])->name('services.show');
Route::get('/visa-support', [PageController::class, 'visaSupport'])->name('visa-support');
Route::get('/reviews', [PageController::class, 'reviews'])->name('reviews.index');
Route::get('/blog', [PageController::class, 'blog'])->name('blog.index');
Route::get('/blog/{post}', [PageController::class, 'blogShow'])->name('blog.show');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/faq', [PageController::class, 'faq'])->name('faq');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/emi', [PageController::class, 'emi'])->name('emi');

Route::get('/cost-estimator', [CostEstimatorController::class, 'index'])->name('cost-estimator.index');
Route::post('/cost-estimator', [CostEstimatorController::class, 'estimate'])->name('cost-estimator.estimate');
Route::get('/compare', [CompareController::class, 'index'])->name('compare.index');
Route::post('/compare', [CompareController::class, 'compare'])->name('compare.run');

Route::get('/track', [TrackController::class, 'index'])->name('track.index');
Route::post('/track', [TrackController::class, 'show'])->name('track.show');

Route::post('/inquiries', [InquiryController::class, 'store'])->name('inquiries.store');
