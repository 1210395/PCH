<?php

use App\Http\Controllers\Academic\AcademicAuthController;
use App\Http\Controllers\Academic\AcademicDashboardController;
use App\Http\Controllers\Academic\AcademicTrainingController;
use App\Http\Controllers\Academic\AcademicWorkshopController;
use App\Http\Controllers\Academic\AcademicAnnouncementController;
use App\Http\Controllers\Academic\AcademicProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Academic CMS Routes
|--------------------------------------------------------------------------
|
| These routes are for the Academic Institution CMS panel.
| Academic accounts (universities, private sectors, colleges) can manage their
| trainings, workshops, and announcements here.
|
*/

Route::prefix('{locale}/academic')->group(function () {

    // ==========================================
    // Protected Routes (Authenticated Academic Users)
    // ==========================================
    // Academic users login through the main login page at /{locale}/login
    Route::middleware(['auth:academic', 'academic', 'throttle:120,1'])->group(function () {

        // Dashboard
        Route::get('/', [AcademicDashboardController::class, 'index'])->name('academic.dashboard');
        Route::get('/dashboard', [AcademicDashboardController::class, 'index']);

        // Logout
        Route::post('/logout', [AcademicAuthController::class, 'logout'])->name('academic.logout');

        // ==========================================
        // Trainings CRUD
        // ==========================================
        Route::prefix('trainings')->group(function () {
            Route::get('/', [AcademicTrainingController::class, 'index'])->name('academic.trainings.index');
            Route::get('/create', [AcademicTrainingController::class, 'create'])->name('academic.trainings.create');
            Route::post('/', [AcademicTrainingController::class, 'store'])->name('academic.trainings.store');
            Route::get('/{id}', [AcademicTrainingController::class, 'show'])->name('academic.trainings.show');
            Route::get('/{id}/edit', [AcademicTrainingController::class, 'edit'])->name('academic.trainings.edit');
            Route::put('/{id}', [AcademicTrainingController::class, 'update'])->name('academic.trainings.update');
            Route::delete('/{id}', [AcademicTrainingController::class, 'destroy'])->name('academic.trainings.destroy');
        });

        // ==========================================
        // Workshops CRUD
        // ==========================================
        Route::prefix('workshops')->group(function () {
            Route::get('/', [AcademicWorkshopController::class, 'index'])->name('academic.workshops.index');
            Route::get('/create', [AcademicWorkshopController::class, 'create'])->name('academic.workshops.create');
            Route::post('/', [AcademicWorkshopController::class, 'store'])->name('academic.workshops.store');
            Route::get('/{id}', [AcademicWorkshopController::class, 'show'])->name('academic.workshops.show');
            Route::get('/{id}/edit', [AcademicWorkshopController::class, 'edit'])->name('academic.workshops.edit');
            Route::put('/{id}', [AcademicWorkshopController::class, 'update'])->name('academic.workshops.update');
            Route::delete('/{id}', [AcademicWorkshopController::class, 'destroy'])->name('academic.workshops.destroy');
        });

        // ==========================================
        // Announcements CRUD
        // ==========================================
        Route::prefix('announcements')->group(function () {
            Route::get('/', [AcademicAnnouncementController::class, 'index'])->name('academic.announcements.index');
            Route::get('/create', [AcademicAnnouncementController::class, 'create'])->name('academic.announcements.create');
            Route::post('/', [AcademicAnnouncementController::class, 'store'])->name('academic.announcements.store');
            Route::get('/{id}', [AcademicAnnouncementController::class, 'show'])->name('academic.announcements.show');
            Route::get('/{id}/edit', [AcademicAnnouncementController::class, 'edit'])->name('academic.announcements.edit');
            Route::put('/{id}', [AcademicAnnouncementController::class, 'update'])->name('academic.announcements.update');
            Route::delete('/{id}', [AcademicAnnouncementController::class, 'destroy'])->name('academic.announcements.destroy');
        });

        // ==========================================
        // Profile / Settings
        // ==========================================
        Route::get('/profile', [AcademicProfileController::class, 'edit'])->name('academic.profile.edit');
        Route::put('/profile', [AcademicProfileController::class, 'update'])->name('academic.profile.update');
        Route::post('/profile/logo', [AcademicProfileController::class, 'uploadLogo'])->name('academic.profile.logo');
        Route::delete('/profile/logo', [AcademicProfileController::class, 'deleteLogo'])->name('academic.profile.logo.delete');
        Route::put('/profile/password', [AcademicProfileController::class, 'updatePassword'])->name('academic.profile.password');
    });
});
