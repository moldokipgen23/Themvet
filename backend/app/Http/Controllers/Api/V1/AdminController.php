<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Exam;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\Question;
use App\Models\MockTest;
use App\Models\Attempt;
use App\Models\ReviewerAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function stats()
    {
        $stats = [
            'total_users' => User::count(),
            'total_students' => User::whereHas('roles', fn($q) => $q->where('name', 'student'))->count(),
            'total_contributors' => User::whereHas('roles', fn($q) => $q->where('name', 'teacher'))->count(),
            'total_exams' => Exam::count(),
            'total_subjects' => Subject::count(),
            'total_topics' => Topic::count(),
            'total_questions' => Question::count(),
            'pending_questions' => Question::where('status', 'pending')->count(),
            'approved_questions' => Question::where('status', 'approved')->count(),
            'total_mock_tests' => MockTest::count(),
            'total_attempts' => Attempt::where('status', 'completed')->count(),
        ];

        return response()->json([
            'status' => 'success',
            'data' => ['stats' => $stats],
        ]);
    }

    public function users(Request $request)
    {
        $query = User::with('roles', 'targetExam');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        if ($request->role) {
            $query->whereHas('roles', fn($q) => $q->where('name', $request->role));
        }

        $users = $query->orderByDesc('created_at')->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => ['users' => $users],
        ]);
    }

    public function assignRole(Request $request, $userId)
    {
        $request->validate([
            'role' => 'required|in:student,teacher,admin',
        ]);

        $user = User::findOrFail($userId);
        $role = Role::where('name', $request->role)->firstOrFail();

        $user->roles()->sync([$role->id]);

        return response()->json([
            'status' => 'success',
            'message' => 'Role assigned successfully',
            'data' => ['user' => $user->load('roles')],
        ]);
    }

    public function storeExam(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $exam = Exam::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Exam created',
            'data' => ['exam' => $exam],
        ], 201);
    }

    public function updateExam(Request $request, $id)
    {
        $exam = Exam::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
            'order' => 'sometimes|integer',
        ]);

        $exam->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Exam updated',
            'data' => ['exam' => $exam],
        ]);
    }

    public function storeSubject(Request $request)
    {
        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $subject = Subject::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Subject created',
            'data' => ['subject' => $subject],
        ], 201);
    }

    public function storeTopic(Request $request)
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $topic = Topic::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Topic created',
            'data' => ['topic' => $topic],
        ], 201);
    }

    public function questions(Request $request)
    {
        $query = Question::with('options', 'contributor', 'reviewer', 'subject', 'topic');

        if ($request->exam_id) {
            $query->where('exam_id', $request->exam_id);
        }

        if ($request->subject_id) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->contributor_id) {
            $query->where('contributor_id', $request->contributor_id);
        }

        $questions = $query->orderByDesc('created_at')->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => ['questions' => $questions],
        ]);
    }

    public function reviewQueue()
    {
        $questions = Question::with('options', 'contributor', 'subject', 'topic', 'exam')
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->paginate(20);

        $stats = [
            'pending' => Question::where('status', 'pending')->count(),
            'approved_today' => Question::where('status', 'approved')
                ->whereDate('updated_at', today())->count(),
            'rejected_today' => Question::where('status', 'rejected')
                ->whereDate('updated_at', today())->count(),
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'questions' => $questions,
                'review_stats' => $stats,
            ],
        ]);
    }

    public function mockTests(Request $request)
    {
        $query = MockTest::with('exam', 'creator');

        if ($request->exam_id) {
            $query->where('exam_id', $request->exam_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $mockTests = $query->orderByDesc('created_at')->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => ['mock_tests' => $mockTests],
        ]);
    }

    public function updateMockTestStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:published,archived',
        ]);

        $mockTest = MockTest::findOrFail($id);
        $mockTest->update(['status' => $request->status]);

        return response()->json([
            'status' => 'success',
            'message' => 'Mock test status updated',
            'data' => ['mock_test' => $mockTest],
        ]);
    }

    public function analytics()
    {
        $analytics = [
            'daily_attempts' => Attempt::where('status', 'completed')
                ->where('created_at', '>=', now()->subDays(7))
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->get(),
            'top_exams' => Exam::withCount('mockTests')
                ->orderByDesc('mock_tests_count')
                ->limit(5)
                ->get(),
        ];

        return response()->json([
            'status' => 'success',
            'data' => ['analytics' => $analytics],
        ]);
    }

    public function assignReviewer(Request $request, $userId)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'subject_id' => 'sometimes|exists:subjects,id',
            'level' => 'sometimes|string|in:reviewer',
        ]);

        $user = User::findOrFail($userId);

        $existing = ReviewerAssignment::where('user_id', $userId)
            ->where('exam_id', $request->exam_id)
            ->where('subject_id', $request->subject_id ?? null)
            ->first();

        if ($existing) {
            if (! $existing->is_active) {
                $existing->update(['is_active' => true]);
                return response()->json([
                    'status' => 'success',
                    'message' => 'Assignment reactivated',
                    'data' => ['assignment' => $existing->load('exam', 'subject')],
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Assignment already exists',
            ], 400);
        }

        $assignment = ReviewerAssignment::create([
            'user_id' => $userId,
            'exam_id' => $request->exam_id,
            'subject_id' => $request->subject_id ?? null,
            'level' => $request->level ?? 'reviewer',
            'is_active' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Reviewer assigned successfully',
            'data' => ['assignment' => $assignment->load('exam', 'subject')],
        ], 201);
    }

    public function removeReviewerAssignment(Request $request, $assignmentId)
    {
        $assignment = ReviewerAssignment::findOrFail($assignmentId);
        $assignment->update(['is_active' => false]);

        return response()->json([
            'status' => 'success',
            'message' => 'Assignment removed',
        ]);
    }

    public function reviewerAssignments(Request $request)
    {
        $query = ReviewerAssignment::with('user', 'exam', 'subject');

        if ($request->exam_id) {
            $query->where('exam_id', $request->exam_id);
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        $assignments = $query->orderByDesc('created_at')->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => ['assignments' => $assignments],
        ]);
    }
}
