<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MockTest;
use App\Models\MockTestSection;
use App\Services\GamificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MockTestController extends Controller
{
    protected $gamification;

    public function __construct(GamificationService $gamification)
    {
        $this->gamification = $gamification;
    }

    public function index(Request $request)
    {
        $request->validate([
            'exam_id' => 'sometimes|exists:exams,id',
            'difficulty' => 'sometimes|in:easy,medium,hard',
        ]);

        $query = MockTest::where('status', 'published')
            ->with(['exam', 'sections', 'questions' => function ($query) {
                $query->with(['question' => function ($q) {
                    $q->with('subject', 'topic');
                }, 'section']);
            }]);

        if ($request->exam_id) {
            $query->where('exam_id', $request->exam_id);
        }

        if ($request->difficulty) {
            $query->where('difficulty', $request->difficulty);
        }

        $mockTests = $query->orderByDesc('created_at')->get();

        return response()->json([
            'status' => 'success',
            'data' => ['mock_tests' => $mockTests],
        ]);
    }

    public function show($id)
    {
        $mockTest = MockTest::where('status', 'published')
            ->with(['exam', 'sections', 'questions' => function ($query) {
                $query->with(['question' => function ($q) {
                    $q->with('subject', 'topic');
                }, 'section']);
            }])
            ->findOrFail($id);

        $mockTest->questions->each(function ($mq) {
            if ($mq->question && $mq->question->relationLoaded('options')) {
                $mq->question->options->each->makeHidden('is_correct');
            }
        });

        return response()->json([
            'status' => 'success',
            'data' => ['mock_test' => $mockTest],
        ]);
    }

    public function start(Request $request, $id)
    {
        $mockTest = MockTest::where('status', 'published')->with('sections')->findOrFail($id);

        if ($mockTest->questions->count() === 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'This test has no questions.',
            ], 422);
        }

        $user = $request->user();

        $existingAttempt = $user->attempts()
            ->where('mock_test_id', $id)
            ->where('status', 'in_progress')
            ->first();

        if ($existingAttempt) {
            $questions = $mockTest->questions->load('question.options');
            $questions->each(function ($mq) {
                if ($mq->question && $mq->question->relationLoaded('options')) {
                    $mq->question->options->each->makeHidden('is_correct');
                }
            });
            $answers = $existingAttempt->answers()->get();
            return response()->json([
                'status' => 'success',
                'data' => [
                    'attempt' => $existingAttempt,
                    'questions' => $questions,
                    'sections' => $mockTest->sections,
                    'answers' => $answers,
                ],
            ]);
        }

        $sectionTimers = [];
        $sectionStatuses = [];
        $firstSectionId = null;

        foreach ($mockTest->sections->sortBy('order') as $section) {
            $sectionTimers[$section->id] = $section->duration_minutes ? $section->duration_minutes * 60 : null;
            $sectionStatuses[$section->id] = 'pending';
            if (!$firstSectionId) {
                $firstSectionId = $section->id;
            }
        }

        $attempt = $user->attempts()->create([
            'mock_test_id' => $id,
            'started_at' => now(),
            'total_marks' => $mockTest->total_marks,
            'status' => 'in_progress',
            'current_section_id' => $firstSectionId,
            'section_time_remaining' => $sectionTimers,
            'section_status' => $sectionStatuses,
        ]);

        $questions = $mockTest->questions->load('question.options');
        $questions->each(function ($mq) {
            if ($mq->question && $mq->question->relationLoaded('options')) {
                $mq->question->options->each->makeHidden('is_correct');
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Test started',
            'data' => [
                'attempt' => $attempt,
                'questions' => $questions,
                'sections' => $mockTest->sections,
                'answers' => [],
            ],
        ], 201);
    }

    public function saveAnswer(Request $request, $id)
    {
        $request->validate([
            'attempt_id' => 'required|exists:attempts,id',
            'question_id' => 'required|exists:questions,id',
            'selected_option_ids' => 'required|array',
            'selected_option_ids.*' => 'integer|exists:question_options,id',
            'time_spent_on_question' => 'sometimes|integer|min:0',
            'is_marked_for_review' => 'sometimes|boolean',
        ]);

        $user = $request->user();

        $attempt = $user->attempts()
            ->where('id', $request->attempt_id)
            ->where('mock_test_id', $id)
            ->where('status', 'in_progress')
            ->firstOrFail();

        $attempt->answers()->updateOrCreate(
            ['question_id' => $request->question_id],
            [
                'selected_option_ids' => $request->selected_option_ids,
                'time_spent_on_question' => $request->time_spent_on_question ?? 0,
                'is_marked_for_review' => $request->boolean('is_marked_for_review', false),
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Answer saved',
        ]);
    }

    public function toggleReview(Request $request, $id)
    {
        $request->validate([
            'attempt_id' => 'required|exists:attempts,id',
            'question_id' => 'required|exists:questions,id',
        ]);

        $user = $request->user();

        $attempt = $user->attempts()
            ->where('id', $request->attempt_id)
            ->where('mock_test_id', $id)
            ->where('status', 'in_progress')
            ->firstOrFail();

        $answer = $attempt->answers()->where('question_id', $request->question_id)->first();

        if ($answer) {
            $answer->update(['is_marked_for_review' => !$answer->is_marked_for_review]);
        } else {
            $attempt->answers()->create([
                'question_id' => $request->question_id,
                'is_marked_for_review' => true,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'is_marked_for_review' => $answer ? !$answer->is_marked_for_review : true,
        ]);
    }

    public function switchSection(Request $request, $id)
    {
        $request->validate([
            'attempt_id' => 'required|exists:attempts,id',
            'section_id' => 'required|exists:mock_test_sections,id',
        ]);

        $user = $request->user();

        $attempt = $user->attempts()
            ->where('id', $request->attempt_id)
            ->where('mock_test_id', $id)
            ->where('status', 'in_progress')
            ->firstOrFail();

        $section = \App\Models\MockTestSection::where('id', $request->section_id)
            ->where('mock_test_id', $id)
            ->firstOrFail();

        $sectionStatuses = $attempt->section_status ?? [];
        if (($sectionStatuses[$section->id] ?? 'pending') === 'submitted') {
            return response()->json(['status' => 'error', 'message' => 'Section already submitted.'], 422);
        }

        $sectionTimeRemaining = $attempt->section_time_remaining ?? [];
        $remaining = $sectionTimeRemaining[$section->id] ?? $section->duration_minutes * 60;
        if ($remaining <= 0) {
            $sectionStatuses[$section->id] = 'submitted';
            $attempt->update(['section_status' => $sectionStatuses]);

            $mockTest = MockTest::with('sections')->findOrFail($id);
            $allSubmitted = true;
            foreach ($mockTest->sections as $s) {
                if (($sectionStatuses[$s->id] ?? 'pending') !== 'submitted') {
                    $allSubmitted = false;
                    break;
                }
            }
            if ($allSubmitted) {
                return $this->doSubmit($request, $id);
            }
            return response()->json(['status' => 'error', 'message' => 'Section timer expired.'], 422);
        }

        $attempt->update(['current_section_id' => $request->section_id]);

        return response()->json([
            'status' => 'success',
            'message' => 'Section switched',
        ]);
    }

    public function submitSection(Request $request, $id)
    {
        $request->validate([
            'attempt_id' => 'required|exists:attempts,id',
            'section_id' => 'required|exists:mock_test_sections,id',
        ]);

        $user = $request->user();

        $attempt = $user->attempts()
            ->where('id', $request->attempt_id)
            ->where('mock_test_id', $id)
            ->where('status', 'in_progress')
            ->firstOrFail();

        \App\Models\MockTestSection::where('id', $request->section_id)
            ->where('mock_test_id', $id)
            ->firstOrFail();

        $sectionStatuses = $attempt->section_status ?? [];
        $sectionStatuses[$request->section_id] = 'submitted';
        $attempt->update(['section_status' => $sectionStatuses]);

        $mockTest = MockTest::with('sections')->findOrFail($id);
        $allSubmitted = true;
        foreach ($mockTest->sections as $section) {
            if (($sectionStatuses[$section->id] ?? 'pending') !== 'submitted') {
                $allSubmitted = false;
                break;
            }
        }

        if ($allSubmitted) {
            return $this->doSubmit($request, $id);
        }

        $nextSection = $mockTest->sections->sortBy('order')
            ->first(function ($s) use ($sectionStatuses) {
                return ($sectionStatuses[$s->id] ?? 'pending') === 'pending';
            });

        if ($nextSection) {
            $attempt->update(['current_section_id' => $nextSection->id]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Section submitted',
            'data' => [
                'section_status' => $sectionStatuses,
                'next_section_id' => $nextSection?->id,
                'all_submitted' => $allSubmitted,
            ],
        ]);
    }

    public function submit(Request $request, $id)
    {
        return $this->doSubmit($request, $id);
    }

    private function doSubmit(Request $request, $id)
    {
        $request->validate([
            'attempt_id' => 'required|exists:attempts,id',
            'answers' => 'sometimes|array',
            'answers.*.question_id' => 'required|integer|exists:questions,id',
            'answers.*.selected_option_ids' => 'required|array',
            'answers.*.selected_option_ids.*' => 'integer|exists:question_options,id',
            'answers.*.time_spent_on_question' => 'sometimes|integer|min:0',
        ]);

        $user = $request->user();

        return DB::transaction(function () use ($request, $user, $id) {

            $attempt = $user->attempts()
                ->where('id', $request->attempt_id)
                ->where('mock_test_id', $id)
                ->where('status', 'in_progress')
                ->lockForUpdate()
                ->firstOrFail();

            $mockTest = MockTest::with('questions.question.options', 'sections')->findOrFail($id);

            $timeSpent = $attempt->started_at->diffInSeconds(now());

            $maxDuration = $mockTest->duration_minutes * 60;
            $isExpired = $timeSpent > $maxDuration;
            if ($isExpired) {
                $timeSpent = $maxDuration;
                $sectionStatuses = $attempt->section_status ?? [];
                foreach ($mockTest->sections as $s) {
                    if (($sectionStatuses[$s->id] ?? 'pending') !== 'submitted') {
                        $sectionStatuses[$s->id] = 'submitted';
                    }
                }
                $attempt->update(['section_status' => $sectionStatuses]);
            }

            if (!empty($request->answers)) {
                foreach ($request->answers as $answer) {
                    $attempt->answers()->updateOrCreate(
                        ['question_id' => $answer['question_id']],
                        [
                            'selected_option_ids' => $answer['selected_option_ids'],
                            'time_spent_on_question' => $answer['time_spent_on_question'] ?? 0,
                        ]
                    );
                }
            }

            $allAnswers = $attempt->answers()->get();
            $mockTestQuestionIds = $mockTest->questions->pluck('question_id')->toArray();

            $correctCount = 0;
            $wrongCount = 0;
            $submittedCount = 0;
            $totalScore = 0;

            foreach ($allAnswers as $answer) {
                if (!in_array($answer->question_id, $mockTestQuestionIds)) {
                    continue;
                }

                $mq = $mockTest->questions->firstWhere('question_id', $answer->question_id);
                if (!$mq || !$mq->question || empty($answer->selected_option_ids)) {
                    continue;
                }

                $submittedCount++;

                $correctOptionIds = $mq->question->options
                    ->where('is_correct', true)
                    ->pluck('id')
                    ->toArray();

                $selectedIds = collect($answer->selected_option_ids)->sort()->values()->toArray();
                $correctIds = collect($correctOptionIds)->sort()->values()->toArray();

                $isCorrect = $selectedIds === $correctIds;

                $answer->update(['is_correct' => $isCorrect]);

                if ($isCorrect) {
                    $correctCount++;
                    $totalScore += $mq->marks;
                } else {
                    $wrongCount++;
                    if ($mockTest->negative_marking) {
                        $totalScore -= $mq->negative_marks;
                    }
                }
            }

            $totalQuestions = $mockTest->questions->count();
            $unattempted = $totalQuestions - $submittedCount;
            $accuracy = $submittedCount > 0 ? ($correctCount / $submittedCount) * 100 : 0;

            $sectionResults = [];
            foreach ($mockTest->sections as $section) {
                $sectionQuestionIds = $mockTest->questions
                    ->where('mock_test_section_id', $section->id)
                    ->pluck('question_id')
                    ->toArray();

                $sectionAnswers = $allAnswers->filter(fn($a) => in_array($a->question_id, $sectionQuestionIds));
                $sectionCorrect = $sectionAnswers->where('is_correct', true)->count();
                $sectionWrong = $sectionAnswers->where('is_correct', false)->count();
                $sectionSubmitted = $sectionAnswers->filter(fn($a) => !empty($a->selected_option_ids))->count();
                $sectionUnattempted = count($sectionQuestionIds) - $sectionSubmitted;

                $sectionResults[$section->id] = [
                    'name' => $section->name,
                    'total' => count($sectionQuestionIds),
                    'submitted' => $sectionSubmitted,
                    'correct' => $sectionCorrect,
                    'wrong' => $sectionWrong,
                    'unattempted' => $sectionUnattempted,
                ];
            }

            $attempt->update([
                'submitted_at' => now(),
                'score' => max(0, $totalScore),
                'accuracy' => round($accuracy, 2),
                'time_spent_seconds' => $timeSpent,
                'status' => $isExpired ? 'expired' : 'completed',
                'section_status' => collect($attempt->section_status ?? [])
                    ->map(fn($s) => 'submitted')
                    ->toArray(),
            ]);

            $difficultyPoints = 0;
            foreach ($allAnswers as $answer) {
                if (!in_array($answer->question_id, $mockTestQuestionIds)) continue;
                $mq = $mockTest->questions->firstWhere('question_id', $answer->question_id);
                if (!$mq || !$mq->question || empty($answer->selected_option_ids)) continue;
                $multiplier = match($mq->question->difficulty) { 'hard' => 20, 'medium' => 15, default => 10 };
                $correctOptionIds = $mq->question->options->where('is_correct', true)->pluck('id')->toArray();
                $selectedIds = collect($answer->selected_option_ids)->sort()->values()->toArray();
                if (collect($correctOptionIds)->sort()->values()->toArray() === $selectedIds) {
                    $difficultyPoints += $multiplier;
                }
            }

            $gamificationResult = $this->gamification->recordTestCompletion(
                $user,
                $submittedCount,
                $correctCount,
                $difficultyPoints
            );

            return response()->json([
                'status' => 'success',
                'message' => $isExpired ? 'Time expired. Test submitted automatically.' : 'Test submitted successfully',
                'data' => [
                    'attempt' => $attempt->load('answers.question'),
                    'summary' => [
                        'total_questions' => $totalQuestions,
                        'submitted' => $submittedCount,
                        'correct' => $correctCount,
                        'wrong' => $wrongCount,
                        'unattempted' => $unattempted,
                        'score' => $attempt->score,
                        'accuracy' => $attempt->accuracy,
                        'time_spent' => $timeSpent,
                        'sections' => $sectionResults,
                    ],
                    'gamification' => $gamificationResult,
                ],
            ]);
        });
    }

    private function buildSummary($attempt, $mockTest, $correct, $wrong, $unattempted, $timeSpent)
    {
        $totalQuestions = $mockTest->questions->count();
        return [
            'attempt' => $attempt->load('answers.question'),
            'summary' => [
                'total_questions' => $totalQuestions,
                'correct' => $correct,
                'wrong' => $wrong,
                'unattempted' => $unattempted ?: $totalQuestions,
                'score' => 0,
                'accuracy' => 0,
                'time_spent' => $timeSpent,
                'sections' => [],
            ],
        ];
    }
}
