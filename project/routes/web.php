<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrganizerController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'ask')->name('home');

Route::post('questions', [QuestionController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('questions.store');

Route::get('organisatrice', [OrganizerController::class, 'show'])->name('organizer.show');
Route::post('organisatrice', [OrganizerController::class, 'unlock'])
    ->middleware('throttle:5,1')
    ->name('organizer.unlock');

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');
    });

Route::middleware(['auth'])->group(function () {
    Route::post('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
    Route::delete('invitations/{invitation}', [TeamInvitationController::class, 'decline'])->name('invitations.decline');
});

require __DIR__.'/settings.php';
