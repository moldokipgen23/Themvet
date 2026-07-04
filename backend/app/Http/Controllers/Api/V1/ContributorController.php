<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\MockTest;
use App\Models\MockTestSection;
use App\Models\MockTestQuestion;
use Illuminate\Http\Request;

class ContributorController extends Controller
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

    public function questions(Request $request)
    {
        $query = $request->user()->questions()
            ->with('options', 'subject', 'topic', 'exam');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $questions = $query->orderByDesc('created_at')->paginate(20);

        $statusCounts = $request->user()->questions()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return response()->json([
            'status' => 'success',
            'data' => [
                'questions' => $questions,
                'status_counts' => [
                    'pending' => $statusCounts['pending'] ?? 0,
                    'approved' => $statusCounts['approved'] ?? 0,
                    'rejected' => $statusCounts['rejected'] ?? 0,
                    'draft' => $statusCounts['draft'] ?? 0,
                ],
            ],
        ]);
    }

    public function questionStats(Request $request)
    {
        $user = $request->user();

        $stats = [
            'total' => $user->questions()->count(),
            'by_status' => $user->questions()
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
            'by_difficulty' => $user->questions()
                ->selectRaw('difficulty, COUNT(*) as count')
                ->groupBy('difficulty')
                ->pluck('count', 'difficulty')
                ->toArray(),
            'by_exam' => $user->questions()
                ->selectRaw('exam_id, COUNT(*) as count')
                ->groupBy('exam_id')
                ->with('exam:id,name')
                ->get()
                ->pluck('count', 'exam.name')
                ->toArray(),
            'recent_activity' => $user->questions()
                ->where('updated_at', '>=', now()->subDays(30))
                ->selectRaw('DATE(updated_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->get(),
        ];

        return response()->json([
            'status' => 'success',
            'data' => ['stats' => $stats],
        ]);
    }

    public function storeQuestion(Request $request)
    {
        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'subject_id' => 'required|exists:subjects,id',
            'topic_id' => 'required|exists:topics,id',
            'question_text' => 'required|string',
            'question_type' => 'sometimes|in:mcq,multi,fill,tf,descriptive',
            'difficulty' => 'sometimes|in:easy,medium,hard',
            'explanation' => 'nullable|string',
            'correct_answer' => 'nullable|string',
            'options' => 'required|array|min:2|max:6',
            'options.*.option_text' => 'required|string',
            'options.*.is_correct' => 'required|boolean',
            'tags' => 'nullable|array',
        ]);

        $hasCorrect = collect($validated['options'])->contains('is_correct', true);
        if (!$hasCorrect) {
            return response()->json([
                'status' => 'error',
                'message' => 'At least one option must be marked as correct.',
            ], 422);
        }

        $question = Question::create([
            'exam_id' => $validated['exam_id'],
            'subject_id' => $validated['subject_id'],
            'topic_id' => $validated['topic_id'],
            'question_text' => $validated['question_text'],
            'question_type' => $validated['question_type'] ?? 'mcq',
            'difficulty' => $validated['difficulty'] ?? 'medium',
            'explanation' => $validated['explanation'] ?? null,
            'correct_answer' => $validated['correct_answer'] ?? null,
            'contributor_id' => $request->user()->id,
            'status' => 'pending',
            'tags' => $validated['tags'] ?? null,
        ]);

        foreach ($validated['options'] as $index => $option) {
            $question->options()->create([
                'option_text' => $option['option_text'],
                'is_correct' => $option['is_correct'],
                'order' => $index + 1,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Question submitted for review',
            'data' => ['question' => $question->load('options')],
        ], 201);
    }

    public function updateQuestion(Request $request, $id)
    {
        $question = $request->user()->questions()->findOrFail($id);

        if (! in_array($question->status, ['draft', 'pending', 'rejected'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot edit this question',
            ], 400);
        }

        $validated = $request->validate([
            'question_text' => 'sometimes|string',
            'question_type' => 'sometimes|in:mcq,multi,fill,tf,descriptive',
            'difficulty' => 'sometimes|in:easy,medium,hard',
            'explanation' => 'nullable|string',
            'correct_answer' => 'nullable|string',
            'options' => 'sometimes|array|min:2|max:6',
            'options.*.option_text' => 'required|string',
            'options.*.is_correct' => 'required|boolean',
            'tags' => 'nullable|array',
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

        $question->update(['status' => 'pending']);

        return response()->json([
            'status' => 'success',
            'message' => 'Question updated and submitted for review',
            'data' => ['question' => $question->load('options')],
        ]);
    }

    public function deleteQuestion(Request $request, $id)
    {
        $question = $request->user()->questions()->findOrFail($id);

        if ($question->status !== 'draft') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only draft questions can be deleted',
            ], 400);
        }

        $question->options()->delete();
        $question->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Question deleted',
        ]);
    }

    public function storeMockTestDraft(Request $request)
    {
        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'exam_pattern_id' => 'nullable|exists:exam_patterns,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'sometimes|integer|min:1',
            'total_marks' => 'sometimes|integer|min:1',
            'total_questions' => 'sometimes|integer|min:1',
            'difficulty' => 'sometimes|in:easy,medium,hard',
            'negative_marking' => 'sometimes|boolean',
            'negative_marking_value' => 'sometimes|numeric|min:0',
            'sections' => 'sometimes|array',
            'sections.*.name' => 'required|string',
            'sections.*.total_questions' => 'required|integer|min:1',
            'sections.*.total_marks' => 'required|integer|min:1',
            'sections.*.duration_minutes' => 'nullable|integer|min:1',
            'sections.*.marks_per_question' => 'sometimes|numeric|min:0',
            'sections.*.negative_marks_per_question' => 'sometimes|numeric|min:0',
            'sections.*.is_mandatory' => 'sometimes|boolean',
        ]);

        $mockTest = $request->user()->createdMockTests()->create([
            'exam_id' => $validated['exam_id'],
            'exam_pattern_id' => $validated['exam_pattern_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'duration_minutes' => $validated['duration_minutes'] ?? 60,
            'total_marks' => $validated['total_marks'] ?? 100,
            'total_questions' => $validated['total_questions'] ?? 0,
            'difficulty' => $validated['difficulty'] ?? 'medium',
            'negative_marking' => $validated['negative_marking'] ?? false,
            'negative_marking_value' => $validated['negative_marking_value'] ?? 0,
            'status' => 'draft',
        ]);

        if (!empty($validated['sections'])) {
            foreach ($validated['sections'] as $index => $section) {
                MockTestSection::create([
                    'mock_test_id' => $mockTest->id,
                    'name' => $section['name'],
                    'total_questions' => $section['total_questions'],
                    'total_marks' => $section['total_marks'],
                    'duration_minutes' => $section['duration_minutes'] ?? null,
                    'marks_per_question' => $section['marks_per_question'] ?? 1,
                    'negative_marks_per_question' => $section['negative_marks_per_question'] ?? 0,
                    'is_mandatory' => $section['is_mandatory'] ?? true,
                    'order' => $index + 1,
                ]);
            }
        }

        $mockTest->update(['total_questions' => $mockTest->sections()->sum('total_questions') ?: $mockTest->total_questions]);

        return response()->json([
            'status' => 'success',
            'message' => 'Mock test draft created',
            'data' => ['mock_test' => $mockTest->load('sections')],
        ], 201);
    }

    public function addQuestionToMockTest(Request $request, $mockTestId)
    {
        $mockTest = $request->user()->createdMockTests()->findOrFail($mockTestId);

        if ($mockTest->status === 'published') {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot modify a published test',
            ], 400);
        }

        $validated = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'mock_test_section_id' => 'nullable|exists:mock_test_sections,id',
            'marks' => 'sometimes|integer|min:1',
            'negative_marks' => 'sometimes|numeric|min:0',
            'order' => 'sometimes|integer|min:0',
        ]);

        $question = Question::findOrFail($validated['question_id']);

        if ($question->status !== 'approved') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only approved questions can be added to mock tests',
            ], 400);
        }

        $existing = MockTestQuestion::where('mock_test_id', $mockTestId)
            ->where('question_id', $validated['question_id'])
            ->first();

        if ($existing) {
            return response()->json([
                'status' => 'error',
                'message' => 'Question already exists in this mock test',
            ], 400);
        }

        $nextOrder = MockTestQuestion::where('mock_test_id', $mockTestId)->max('order') + 1;

        $mtq = MockTestQuestion::create([
            'mock_test_id' => $mockTestId,
            'question_id' => $validated['question_id'],
            'mock_test_section_id' => $validated['mock_test_section_id'] ?? null,
            'marks' => $validated['marks'] ?? 1,
            'negative_marks' => $validated['negative_marks'] ?? 0,
            'order' => $validated['order'] ?? $nextOrder,
        ]);

        $totalQuestions = MockTestQuestion::where('mock_test_id', $mockTestId)->count();
        $totalMarks = MockTestQuestion::where('mock_test_id', $mockTestId)->sum('marks');
        $mockTest->update(['total_questions' => $totalQuestions, 'total_marks' => $totalMarks]);

        return response()->json([
            'status' => 'success',
            'message' => 'Question added to mock test',
            'data' => ['mock_test_question' => $mtq->load('question')],
        ], 201);
    }

    public function removeQuestionFromMockTest(Request $request, $mockTestId, $questionId)
    {
        $mockTest = $request->user()->createdMockTests()->findOrFail($mockTestId);

        if ($mockTest->status === 'published') {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot modify a published test',
            ], 400);
        }

        MockTestQuestion::where('mock_test_id', $mockTestId)
            ->where('question_id', $questionId)
            ->delete();

        $totalQuestions = MockTestQuestion::where('mock_test_id', $mockTestId)->count();
        $totalMarks = MockTestQuestion::where('mock_test_id', $mockTestId)->sum('marks');
        $mockTest->update(['total_questions' => $totalQuestions, 'total_marks' => $totalMarks]);

        return response()->json([
            'status' => 'success',
            'message' => 'Question removed from mock test',
        ]);
    }

    public function mockTestDetail(Request $request, $mockTestId)
    {
        $mockTest = $request->user()->createdMockTests()
            ->with(['sections', 'questions' => function ($query) {
                $query->with(['question' => function ($q) {
                    $q->with('subject', 'topic');
                }, 'section']);
            }])
            ->findOrFail($mockTestId);

        return response()->json([
            'status' => 'success',
            'data' => ['mock_test' => $mockTest],
        ]);
    }
}
