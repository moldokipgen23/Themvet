<?php

use App\Http\Controllers\Admin\AdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('admin.login'));
Route::get('/login', fn () => redirect()->route('admin.login'))->name('login');

Route::get('/admin/login', [AdminController::class, 'showLogin'])
    ->name('admin.login');

Route::post('/admin/login', [AdminController::class, 'login'])
    ->name('admin.login.post');

Route::post('/admin/logout', [AdminController::class, 'logout'])
    ->name('admin.logout')
    ->middleware('auth');

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])
        ->name('dashboard')
        ->middleware('admin');

    Route::get('/users', [AdminController::class, 'users'])
        ->name('users.index')
        ->middleware('admin');

    Route::post('/users/{id}/role', [AdminController::class, 'assignRole'])
        ->name('users.role')
        ->middleware('admin');

    Route::get('/exams', [AdminController::class, 'exams'])
        ->name('exams.index')
        ->middleware('admin');

    Route::post('/exams', [AdminController::class, 'storeExam'])
        ->name('exams.store')
        ->middleware('admin');

    Route::get('/exams/{id}/edit', [AdminController::class, 'editExam'])
        ->name('exams.edit')
        ->middleware('admin');

    Route::put('/exams/{id}', [AdminController::class, 'updateExam'])
        ->name('exams.update')
        ->middleware('admin');

    Route::get('/exams/{examId}/subjects', [AdminController::class, 'examSubjects'])
        ->name('exams.subjects')
        ->middleware('admin');

    Route::get('/questions', [AdminController::class, 'questions'])
        ->name('questions.index')
        ->middleware('admin');

    Route::get('/questions/{id}', [AdminController::class, 'showQuestion'])
        ->name('questions.show')
        ->middleware('admin');

    Route::get('/questions/{id}/edit', [AdminController::class, 'editQuestion'])
        ->name('questions.edit')
        ->middleware('admin');

    Route::put('/questions/{id}', [AdminController::class, 'updateQuestion'])
        ->name('questions.update')
        ->middleware('admin');

    Route::delete('/questions/{id}', [AdminController::class, 'destroyQuestion'])
        ->name('questions.destroy')
        ->middleware('admin');

    Route::get('/mock-tests', [AdminController::class, 'mockTests'])
        ->name('mock-tests.index')
        ->middleware('admin');

    Route::post('/mock-tests', [AdminController::class, 'storeMockTest'])
        ->name('mock-tests.store')
        ->middleware('admin');

    Route::put('/mock-tests/{id}/status', [AdminController::class, 'updateMockTestStatus'])
        ->name('mock-tests.status')
        ->middleware('admin');

    Route::get('/mock-tests/{id}/edit', [AdminController::class, 'editMockTest'])
        ->name('mock-tests.edit')
        ->middleware('admin');

    Route::put('/mock-tests/{id}', [AdminController::class, 'updateMockTest'])
        ->name('mock-tests.update')
        ->middleware('admin');

    Route::delete('/mock-tests/{id}', [AdminController::class, 'destroyMockTest'])
        ->name('mock-tests.destroy')
        ->middleware('admin');

    // Reviewer Assignments
    Route::get('/reviewer-assignments', [AdminController::class, 'reviewerAssignments'])
        ->name('reviewer-assignments.index')
        ->middleware('admin');

    Route::post('/reviewer-assignments', [AdminController::class, 'storeReviewerAssignment'])
        ->name('reviewer-assignments.store')
        ->middleware('admin');

    Route::delete('/reviewer-assignments/{id}', [AdminController::class, 'destroyReviewerAssignment'])
        ->name('reviewer-assignments.destroy')
        ->middleware('admin');

                    // Settings
    Route::get('/settings', [AdminController::class, 'settings'])
        ->name('settings')
        ->middleware('admin');
    Route::post('/settings', [AdminController::class, 'updateSettings'])
        ->name('settings.update')
        ->middleware('admin');

    // Analytics
    Route::get('/analytics', [AdminController::class, 'analytics'])
        ->name('analytics')
        ->middleware('admin');

    // Review Queue
    Route::get('/review-queue', [AdminController::class, 'reviewQueue'])
        ->name('review-queue')
        ->middleware('admin');

    Route::post('/questions/{id}/approve', [AdminController::class, 'approveQuestion'])
        ->name('questions.approve')
        ->middleware('admin');

    Route::post('/questions/{id}/reject', [AdminController::class, 'rejectQuestion'])
        ->name('questions.reject')
        ->middleware('admin');

    // Subjects CRUD
    Route::get('/subjects', [AdminController::class, 'subjects'])
        ->name('subjects.index')
        ->middleware('admin');
    Route::post('/subjects', [AdminController::class, 'storeSubject'])
        ->name('subjects.store')
        ->middleware('admin');
    Route::put('/subjects/{id}', [AdminController::class, 'updateSubject'])
        ->name('subjects.update')
        ->middleware('admin');
    Route::delete('/subjects/{id}', [AdminController::class, 'destroySubject'])
        ->name('subjects.destroy')
        ->middleware('admin');

    // Topics CRUD
    Route::get('/topics', [AdminController::class, 'topics'])
        ->name('topics.index')
        ->middleware('admin');
    Route::post('/topics', [AdminController::class, 'storeTopic'])
        ->name('topics.store')
        ->middleware('admin');
    Route::put('/topics/{id}', [AdminController::class, 'updateTopic'])
        ->name('topics.update')
        ->middleware('admin');
    Route::delete('/topics/{id}', [AdminController::class, 'destroyTopic'])
        ->name('topics.destroy')
        ->middleware('admin');

    // Roles
    Route::get('/roles', [AdminController::class, 'roles'])
        ->name('roles.index')
        ->middleware('admin');

    // Notifications
    Route::get('/notifications', [AdminController::class, 'notifications'])
        ->name('notifications.index')
        ->middleware('admin');
    Route::post('/notifications', [AdminController::class, 'storeNotification'])
        ->name('notifications.store')
        ->middleware('admin');
});
