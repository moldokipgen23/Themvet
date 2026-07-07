<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Models\User;
use App\Services\GamificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttemptController extends Controller
{
    protected $gamification;

    public function __construct(GamificationService $gamification)
    {
        $this->gamification = $gamification;
    }

    public function index(Request $request)
    {
        $attempts = $request->user()->attempts()
            ->with('mockTest.exam')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => ['attempts' => $attempts],
        ]);
    }

    public function show(Request $request, $id)
    {
        $attempt = $request->user()->attempts()
            ->with(['mockTest.exam', 'answers.question.options'])
            ->findOrFail($id);

        $correctCount = $attempt->answers->where('is_correct', true)->count();
        $wrongCount = $attempt->answers->where('is_correct', false)->count();
        $totalQuestions = $attempt->mockTest->questions->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'attempt' => $attempt,
                'summary' => [
                    'total_questions' => $totalQuestions,
                    'correct' => $correctCount,
                    'wrong' => $wrongCount,
                    'unattempted' => $totalQuestions - $correctCount - $wrongCount,
                    'score' => $attempt->score,
                    'accuracy' => $attempt->accuracy,
                    'time_spent' => $attempt->time_spent_seconds,
                ],
            ],
        ]);
    }

    public function summary(Request $request)
    {
        $user = $request->user();

        $totalAttempts = $user->attempts()->where('status', 'completed')->count();
        $averageScore = $user->attempts()->where('status', 'completed')->avg('score');
        $averageAccuracy = $user->attempts()->where('status', 'completed')->avg('accuracy');

        $gamificationStats = $this->gamification->getUserStats($user);

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_attempts' => $totalAttempts,
                'average_score' => round($averageScore ?? 0, 2),
                'average_accuracy' => round($averageAccuracy ?? 0, 2),
                'gamification' => $gamificationStats,
            ],
        ]);
    }

    public function progress(Request $request)
    {
        $attempts = $request->user()->attempts()
            ->where('status', 'completed')
            ->with('mockTest.exam')
            ->orderBy('created_at')
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'date' => $a->created_at->format('Y-m-d'),
                'score' => $a->score,
                'accuracy' => $a->accuracy,
                'test_title' => $a->mockTest?->title,
                'exam_name' => $a->mockTest?->exam?->name,
            ]);

        $percentile = null;
        $userBest = $request->user()->attempts()
            ->where('status', 'completed')
            ->max('score') ?? 0;

        $totalUsers = User::whereHas('attempts', fn($q) => $q->where('status', 'completed'))->count();
        if ($totalUsers > 0) {
            $usersBetter = DB::table('users')
                ->whereExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('attempts')
                        ->whereColumn('users.id', 'attempts.user_id')
                        ->where('status', 'completed');
                })
                ->whereRaw('(SELECT COALESCE(MAX(score), 0) FROM attempts WHERE user_id = users.id AND status = ?) > ?', ['completed', $userBest])
                ->count();
            $percentile = round((($totalUsers - $usersBetter) / $totalUsers) * 100, 1);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'attempts' => $attempts,
                'percentile' => $percentile,
                'total_attempts' => $attempts->count(),
                'best_score' => $attempts->max('score') ?? 0,
                'average_accuracy' => round($attempts->avg('accuracy') ?? 0, 1),
            ],
        ]);
    }

    public function recordCompletion(Request $request, $id)
    {
        $attempt = $request->user()->attempts()
            ->where('id', $id)
            ->where('status', 'completed')
            ->firstOrFail();

        $totalQuestions = $attempt->mockTest->questions->count();
        $correctAnswers = $attempt->answers->where('is_correct', true)->count();

        $result = $this->gamification->recordTestCompletion(
            $request->user(),
            $totalQuestions,
            $correctAnswers
        );

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ]);
    }
}
