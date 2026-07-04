<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ExamController;
use App\Http\Controllers\Api\V1\QuestionController;
use App\Http\Controllers\Api\V1\MockTestController;
use App\Http\Controllers\Api\V1\AttemptController;
use App\Http\Controllers\Api\V1\ContributorController;
use App\Http\Controllers\Api\V1\ReviewerController;
use App\Http\Controllers\Api\V1\AdminController;
use App\Http\Controllers\Api\V1\LeaderboardController;
use App\Http\Controllers\Api\V1\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public Routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp']);

// Google OAuth
Route::get('/auth/google', [AuthController::class, 'googleRedirect']);
Route::match(['get', 'post'], '/auth/google/callback', [AuthController::class, 'googleCallback']);

// Email Verification
Route::get('/auth/verify-email', [AuthController::class, 'verifyEmail']);

// Public Settings
Route::get('/settings', [\App\Http\Controllers\Admin\AdminController::class, 'getSettings']);

// Public Exam Routes
Route::get('/exams', [ExamController::class, 'index']);
Route::get('/exams/{id}', [ExamController::class, 'show']);
Route::get('/exams/{examId}/subjects', [ExamController::class, 'subjects']);
Route::get('/exams/{examId}/subjects/{subjectId}/topics', [ExamController::class, 'topics']);

// Public standalone subject/topics route
Route::get('/subjects/{id}/topics', [ExamController::class, 'subjectTopics']);

// Public Exam Pattern route
Route::get('/exam-patterns/{id}', [ExamController::class, 'pattern']);

// Authenticated Routes
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/profile', [AuthController::class, 'profile']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::put('/auth/change-password', [AuthController::class, 'changePassword']);
    Route::post('/auth/send-verification-email', [AuthController::class, 'sendVerificationEmail']);

    // Device Tokens (Push Notifications)
    Route::post('/device-token', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'token' => 'required|string',
            'platform' => 'sometimes|string|in:android,ios,web',
            'device_name' => 'nullable|string',
        ]);
        app(\App\Services\PushNotificationService::class)->registerToken(
            $request->user(),
            $request->token,
            $request->platform ?? 'android',
            $request->device_name
        );
        return response()->json(['status' => 'success', 'message' => 'Device token registered.']);
    });

    Route::delete('/device-token', function (\Illuminate\Http\Request $request) {
        $request->validate(['token' => 'required|string']);
        app(\App\Services\PushNotificationService::class)->removeToken($request->token);
        return response()->json(['status' => 'success', 'message' => 'Device token removed.']);
    });

    // Questions (Practice)
    Route::get('/questions/practice', [QuestionController::class, 'practice']);
    Route::get('/questions/{id}', [QuestionController::class, 'show']);

    // Mock Tests
    Route::get('/mock-tests', [MockTestController::class, 'index']);
    Route::get('/mock-tests/{id}', [MockTestController::class, 'show']);
    Route::post('/mock-tests/{id}/start', [MockTestController::class, 'start']);
    Route::post('/mock-tests/{id}/save-answer', [MockTestController::class, 'saveAnswer']);
    Route::post('/mock-tests/{id}/toggle-review', [MockTestController::class, 'toggleReview']);
    Route::post('/mock-tests/{id}/switch-section', [MockTestController::class, 'switchSection']);
    Route::post('/mock-tests/{id}/submit-section', [MockTestController::class, 'submitSection']);
    Route::post('/mock-tests/{id}/submit', [MockTestController::class, 'submit']);

    // Attempts & Results
    Route::get('/attempts', [AttemptController::class, 'index']);
    Route::get('/attempts/{id}', [AttemptController::class, 'show']);
    Route::get('/results/summary', [AttemptController::class, 'summary']);
    Route::get('/results/progress', [AttemptController::class, 'progress']);

    // Leaderboard
    Route::get('/leaderboard', [LeaderboardController::class, 'index']);
    Route::get('/leaderboard/my-stats', [LeaderboardController::class, 'myStats']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);

    // Contributor Routes
    Route::prefix('contributor')->group(function () {
        Route::get('/questions', [ContributorController::class, 'questions']);
        Route::get('/stats', [ContributorController::class, 'questionStats']);
        Route::post('/questions', [ContributorController::class, 'storeQuestion']);
        Route::put('/questions/{id}', [ContributorController::class, 'updateQuestion']);
        Route::delete('/questions/{id}', [ContributorController::class, 'deleteQuestion']);
        Route::post('/mock-tests', [ContributorController::class, 'storeMockTestDraft']);
        Route::get('/mock-tests/{id}', [ContributorController::class, 'mockTestDetail']);
        Route::post('/mock-tests/{id}/questions', [ContributorController::class, 'addQuestionToMockTest']);
        Route::delete('/mock-tests/{mockTestId}/questions/{questionId}', [ContributorController::class, 'removeQuestionFromMockTest']);
    });

    // Reviewer Routes
    Route::prefix('reviewer')->group(function () {
        Route::get('/queue', [ReviewerController::class, 'queue']);
        Route::get('/questions/{id}', [ReviewerController::class, 'showQuestion']);
        Route::put('/questions/{id}', [ReviewerController::class, 'updateQuestion']);
        Route::post('/questions/{id}/approve', [ReviewerController::class, 'approve']);
        Route::post('/questions/{id}/reject', [ReviewerController::class, 'reject']);
        Route::post('/bulk-approve', [ReviewerController::class, 'bulkApprove']);
        Route::post('/bulk-reject', [ReviewerController::class, 'bulkReject']);
        Route::post('/mock-tests', [ReviewerController::class, 'storeMockTest']);
        Route::post('/mock-tests/{id}/publish', [ReviewerController::class, 'publishMockTest']);
        Route::get('/my-assignments', [ReviewerController::class, 'myAssignments']);
    });

    // Admin Routes
    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::get('/stats', [AdminController::class, 'stats']);
        Route::get('/users', [AdminController::class, 'users']);
        Route::post('/users/{id}/role', [AdminController::class, 'assignRole']);
        Route::post('/exams', [AdminController::class, 'storeExam']);
        Route::put('/exams/{id}', [AdminController::class, 'updateExam']);
        Route::post('/subjects', [AdminController::class, 'storeSubject']);
        Route::post('/topics', [AdminController::class, 'storeTopic']);
        Route::get('/questions', [AdminController::class, 'questions']);
        Route::get('/review-queue', [AdminController::class, 'reviewQueue']);
        Route::get('/mock-tests', [AdminController::class, 'mockTests']);
        Route::put('/mock-tests/{id}/status', [AdminController::class, 'updateMockTestStatus']);
        Route::get('/analytics', [AdminController::class, 'analytics']);
        Route::post('/reviewers/assign/{userId}', [AdminController::class, 'assignReviewer']);
        Route::delete('/reviewers/assignments/{assignmentId}', [AdminController::class, 'removeReviewerAssignment']);
        Route::get('/reviewer-assignments', [AdminController::class, 'reviewerAssignments']);
    });
});
