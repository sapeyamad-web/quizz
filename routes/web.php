<?php

use App\Http\Controllers\ExamSessionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ExamSessionController::class, 'home'])->name('home');

Route::get('/sessions/{examSession}', [ExamSessionController::class, 'publicShow'])->name('sessions.public');
Route::post('/sessions/{examSession}/start', [ExamSessionController::class, 'start'])->name('sessions.start');

Route::get('/exam/{examAttempt}', [ExamSessionController::class, 'take'])->name('exam.take');
Route::post('/exam/{examAttempt}/answer', [ExamSessionController::class, 'saveAnswer'])->name('exam.answer');
Route::post('/exam/{examAttempt}/submit', [ExamSessionController::class, 'submit'])->name('exam.submit');
Route::get('/exam/{examAttempt}/result', [ExamSessionController::class, 'result'])->name('exam.result');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [ExamSessionController::class, 'dashboard'])->name('dashboard');

    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', [ExamSessionController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/sessions/create', [ExamSessionController::class, 'create'])->name('admin.sessions.create');
        Route::post('/sessions', [ExamSessionController::class, 'store'])->name('admin.sessions.store');
        Route::get('/sessions/{examSession}', [ExamSessionController::class, 'show'])->name('admin.sessions.show');
        Route::get('/sessions/{examSession}/results', [ExamSessionController::class, 'results'])->name('admin.sessions.results');
        Route::get('/sessions/{examSession}/results/export', [ExamSessionController::class, 'exportResults'])->name('admin.sessions.results.export');
        Route::delete('/sessions/{examSession}/attempts/{examAttempt}', [ExamSessionController::class, 'destroyAttempt'])->name('admin.sessions.attempts.destroy');
        Route::post('/sessions/{examSession}/questions', [ExamSessionController::class, 'storeQuestion'])->name('admin.sessions.questions.store');
        Route::get('/sessions/{examSession}/live', [ExamSessionController::class, 'live'])->name('admin.sessions.live');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
