<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\GamificationService;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    protected $gamification;

    public function __construct(GamificationService $gamification)
    {
        $this->gamification = $gamification;
    }

    public function index(Request $request)
    {
        $request->validate([
            'period' => 'sometimes|in:daily,weekly,all_time',
            'exam_id' => 'sometimes|exists:exams,id',
        ]);

        $period = $request->get('period', 'daily');
        $examId = $request->get('exam_id');

        $leaderboard = $this->gamification->getLeaderboard($period, $examId);

        $userRank = null;
        if ($request->user()) {
            $userRank = $this->gamification->getUserRank($request->user(), $period);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'leaderboard' => $leaderboard,
                'user_rank' => $userRank,
                'period' => $period,
            ],
        ]);
    }

    public function myStats(Request $request)
    {
        $stats = $this->gamification->getUserStats($request->user());

        return response()->json([
            'status' => 'success',
            'data' => $stats,
        ]);
    }
}
