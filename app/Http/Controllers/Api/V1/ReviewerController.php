<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionReview;
use App\Models\MockTest;
use App\Models\MockTestQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewerController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!$request->user() || !$request->user()->isTeacher()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Teacher access required.',
                ], 403);
            }
            return $next($request);
        });
    }

    public function queue(Request $request)
    {
        $request->validate([
            'exam_id' => 'sometimes|exists:exams,id',
            'subject_id' => 'sometimes|exists:subjects,id',
            'status' => 'sometimes|in:pending,approved,rejected',
            'difficulty' => 'sometimes|in:easy,medium,hard',
            'contributor_id' => 'sometimes|exists:users,id',
        ]);

        $user = $request->user();
        $query = Question::with('options', 'contributor', 'subject', 'topic', 'exam');

        $query->whereHas('exam.reviewerAssignments', function ($q) use ($user) {
            $q->where('user_id', $user->id)->where('is_active', true);
        });

        if ($request->status) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'pending');
        }

        if ($request->exam_id) {
            $query->where('exam_id', $request->exam_id);
        }

        if ($request->subject_id) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->difficulty) {
            $query->where('difficulty', $request->difficulty);
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

    public function showQuestion($id)
    {
        $question = Question::with([
            'options',
            'contributor',
            'reviews.reviewer',
            'subject',
            'topic',
            'exam',
        ])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => ['question' => $question],
        ]);
    }

    public function updateQuestion(Request $request, $id)
    {
        $question = Question::findOrFail($id);

        $validated = $request->validate([
            'question_text' => 'sometimes|string',
            'question_type' => 'sometimes|in:mcq,multi,fill,tf,descriptive',
            'difficulty' => 'sometimes|in:easy,medium,hard',
            'explanation' => 'nullable|string',
            'correct_answer' => 'nullable|string',
            'options' => 'sometimes|array|min:2|max:6',
            'options.*.option_text' => 'required|string',
            'options.*.is_correct' => 'required|boolean',
        ]);

        $question->update(collect($validated)->except('options')->toArray());

        if (isset($validated['options'])) {
            $question->options()->delete();
            foreach ($validated['options'] as $index => $option) {
                $question->options()->create([
                    'option_text' => $option['option_text'],
                    'is_correct' => $option['is_correct'],
                    'order' => $index + 1,
                ]);
            }
        }

        $question->update(['reviewer_id' => $request->user()->id]);

        QuestionReview::create([
            'question_id' => $id,
            'reviewer_id' => $request->user()->id,
            'action' => 'edited',
            'comments' => $request->input('review_comment', 'Edited by reviewer'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Question updated by reviewer',
            'data' => ['question' => $question->load('options')],
        ]);
    }

    public function approve(Request $request, $id)
    {
        $question = Question::findOrFail($id);

        if ($question->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Question is not pending review',
            ], 400);
        }

        $request->validate([
            'comments' => 'nullable|string',
        ]);

        $question->update([
            'status' => 'approved',
            'reviewer_id' => $request->user()->id,
        ]);

        QuestionReview::create([
            'question_id' => $id,
            'reviewer_id' => $request->user()->id,
            'action' => 'approved',
            'comments' => $request->comments ?? null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Question approved',
            'data' => ['question' => $question->load('options')],
        ]);
    }

    public function reject(Request $request, $id)
    {
        $question = Question::findOrFail($id);

        if ($question->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Question is not pending review',
            ], 400);
        }

        $request->validate([
            'comments' => 'required|string',
        ]);

        $question->update([
            'status' => 'rejected',
            'reviewer_id' => $request->user()->id,
        ]);

        QuestionReview::create([
            'question_id' => $id,
            'reviewer_id' => $request->user()->id,
            'action' => 'rejected',
            'comments' => $request->comments,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Question rejected',
            'data' => ['question' => $question->load('options')],
        ]);
    }

    public function bulkApprove(Request $request)
    {
        $request->validate([
            'question_ids' => 'required|array|min:1',
            'question_ids.*' => 'exists:questions,id',
            'comments' => 'nullable|string',
        ]);

        $user = $request->user();
        $questions = Question::whereIn('id', $request->question_ids)
            ->where('status', 'pending')
            ->get();

        $approved = 0;
        $skipped = 0;

        DB::beginTransaction();

        try {
            foreach ($questions as $question) {
                $question->update([
                    'status' => 'approved',
                    'reviewer_id' => $user->id,
                ]);

                QuestionReview::create([
                    'question_id' => $question->id,
                    'reviewer_id' => $user->id,
                    'action' => 'approved',
                    'comments' => $request->comments ?? null,
                ]);

                $approved++;
            }

            $skipped = count($request->question_ids) - $approved;

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to approve questions',
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => "{$approved} questions approved" . ($skipped > 0 ? ", {$skipped} skipped" : ''),
            'data' => [
                'approved' => $approved,
                'skipped' => $skipped,
            ],
        ]);
    }

    public function bulkReject(Request $request)
    {
        $request->validate([
            'question_ids' => 'required|array|min:1',
            'question_ids.*' => 'exists:questions,id',
            'comments' => 'required|string',
        ]);

        $user = $request->user();
        $questions = Question::whereIn('id', $request->question_ids)
            ->where('status', 'pending')
            ->get();

        $rejected = 0;
        $skipped = 0;

        DB::beginTransaction();

        try {
            foreach ($questions as $question) {
                $question->update([
                    'status' => 'rejected',
                    'reviewer_id' => $user->id,
                ]);

                QuestionReview::create([
                    'question_id' => $question->id,
                    'reviewer_id' => $user->id,
                    'action' => 'rejected',
                    'comments' => $request->comments,
                ]);

                $rejected++;
            }

            $skipped = count($request->question_ids) - $rejected;

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to reject questions',
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => "{$rejected} questions rejected" . ($skipped > 0 ? ", {$skipped} skipped" : ''),
            'data' => [
                'rejected' => $rejected,
                'skipped' => $skipped,
            ],
        ]);
    }

    public function storeMockTest(Request $request)
    {
        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1',
            'total_marks' => 'required|integer|min:1',
            'negative_marking' => 'sometimes|boolean',
            'negative_marking_value' => 'sometimes|numeric|min:0',
            'question_ids' => 'required|array|min:1',
            'question_ids.*' => 'exists:questions,id',
        ]);

        $mockTest = MockTest::create([
            'exam_id' => $validated['exam_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'duration_minutes' => $validated['duration_minutes'],
            'total_marks' => $validated['total_marks'],
            'negative_marking' => $validated['negative_marking'] ?? false,
            'negative_marking_value' => $validated['negative_marking_value'] ?? 0,
            'is_official' => $request->user()->isTeacher(),
            'created_by' => $request->user()->id,
            'status' => 'draft',
        ]);

        $totalQuestions = count($validated['question_ids']);
        $baseMarks = intdiv($validated['total_marks'], $totalQuestions);
        $remainder = $validated['total_marks'] - ($baseMarks * $totalQuestions);

        foreach ($validated['question_ids'] as $index => $questionId) {
            $marks = $baseMarks + ($index < $remainder ? 1 : 0);
            MockTestQuestion::create([
                'mock_test_id' => $mockTest->id,
                'question_id' => $questionId,
                'marks' => $marks,
                'negative_marks' => $validated['negative_marking'] ? round($marks * 0.25, 2) : 0,
                'order' => $index + 1,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Mock test created as draft',
            'data' => ['mock_test' => $mockTest->load('questions.question')],
        ], 201);
    }

    public function publishMockTest(Request $request, $id)
    {
        $mockTest = MockTest::findOrFail($id);

        if ($mockTest->status !== 'draft') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only draft tests can be published',
            ], 400);
        }

        if ($mockTest->questions()->count() === 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot publish a test with no questions',
            ], 400);
        }

        $mockTest->update(['status' => 'published']);

        return response()->json([
            'status' => 'success',
            'message' => 'Mock test published',
            'data' => ['mock_test' => $mockTest],
        ]);
    }

    public function myAssignments(Request $request)
    {
        $assignments = $request->user()
            ->reviewerAssignments()
            ->with('exam', 'subject')
            ->active()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => ['assignments' => $assignments],
        ]);
    }
}
