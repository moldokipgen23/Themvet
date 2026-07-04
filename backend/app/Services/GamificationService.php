<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserStreak;
use App\Models\UserStat;
use App\Models\UserBadge;
use App\Models\Badge;
use App\Models\LeaderboardSnapshot;
use App\Models\Attempt;
use App\Models\MockTestQuestion;
use Carbon\Carbon;

class GamificationService
{
    public function recordActivity(User $user)
    {
        $streak = UserStreak::firstOrCreate(
            ['user_id' => $user->id],
            ['current_streak' => 0, 'longest_streak' => 0, 'last_activity_date' => now()]
        );
        $streak->recordActivity();
        return $streak;
    }

    public function recordTestCompletion(User $user, int $totalQuestions, int $correctAnswers, ?int $pointsOverride = null)
    {
        // Update streak
        $this->recordActivity($user);

        // Update stats
        $stat = UserStat::firstOrCreate(
            ['user_id' => $user->id],
            ['total_tests_taken' => 0, 'total_questions_attempted' => 0, 'total_correct_answers' => 0, 'total_points' => 0]
        );
        $points = $stat->recordTestAttempt($totalQuestions, $correctAnswers, $pointsOverride);

        // Update leaderboard
        $this->updateLeaderboard($user);

        // Check for new badges
        $newBadges = $this->checkAndAwardBadges($user);

        $streak = UserStreak::where('user_id', $user->id)->first();

        return [
            'points_earned' => $points,
            'current_streak' => $streak?->current_streak ?? 0,
            'badges_earned' => $newBadges,
            'new_badges' => $newBadges,
        ];
    }

    public function updateLeaderboard(User $user)
    {
        $today = Carbon::today();

        $completedAttempts = Attempt::where('user_id', $user->id)
            ->where('status', 'completed');

        // Daily leaderboard
        $dailyScore = (clone $completedAttempts)
            ->whereDate('created_at', $today)
            ->sum('score');

        LeaderboardSnapshot::updateOrCreate(
            [
                'user_id' => $user->id,
                'period' => 'daily',
                'date' => $today,
            ],
            ['score_metric' => $dailyScore]
        );

        // Weekly leaderboard
        $weekStart = $today->copy()->startOfWeek();
        $weeklyScore = (clone $completedAttempts)
            ->where('created_at', '>=', $weekStart)
            ->sum('score');

        LeaderboardSnapshot::updateOrCreate(
            [
                'user_id' => $user->id,
                'period' => 'weekly',
                'date' => $today,
            ],
            ['score_metric' => $weeklyScore]
        );

        // All-time leaderboard
        $allTimeScore = (clone $completedAttempts)->sum('score');

        LeaderboardSnapshot::updateOrCreate(
            [
                'user_id' => $user->id,
                'period' => 'all_time',
                'date' => $today,
            ],
            ['score_metric' => $allTimeScore]
        );

        $this->recalculateRanks();
    }

    public function recalculateRanks()
    {
        foreach (['daily', 'weekly', 'all_time'] as $period) {
            $today = Carbon::today();
            $snapshots = LeaderboardSnapshot::where('period', $period)
                ->where('date', $today)
                ->orderByDesc('score_metric')
                ->get();

            foreach ($snapshots as $index => $snapshot) {
                $snapshot->update(['rank' => $index + 1]);
            }
        }
    }

    public function getLeaderboard(string $period = 'daily', ?int $examId = null, int $limit = 50)
    {
        $query = LeaderboardSnapshot::with('user')
            ->where('period', $period)
            ->where('date', Carbon::today());

        if ($examId) {
            $query->where('exam_id', $examId);
        }

        return $query->orderBy('rank')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'rank' => $item->rank,
                    'user_id' => $item->user_id,
                    'name' => $item->user->name,
                    'score' => $item->score_metric,
                ];
            });
    }

    public function getUserRank(User $user, string $period = 'daily')
    {
        $snapshot = LeaderboardSnapshot::where('user_id', $user->id)
            ->where('period', $period)
            ->where('date', Carbon::today())
            ->first();

        return $snapshot?->rank;
    }

    public function checkAndAwardBadges(User $user)
    {
        $newBadges = [];
        $stat = UserStat::where('user_id', $user->id)->first();
        $streak = UserStreak::where('user_id', $user->id)->first();

        if (! $stat) return $newBadges;

        $allBadges = Badge::all();

        foreach ($allBadges as $badge) {
            $alreadyEarned = UserBadge::where('user_id', $user->id)
                ->where('badge_id', $badge->id)
                ->exists();

            if ($alreadyEarned) continue;

            $earned = false;

            switch ($badge->name) {
                case 'first_test':
                    $earned = $stat->total_tests_taken >= 1;
                    break;
                case 'five_tests':
                    $earned = $stat->total_tests_taken >= 5;
                    break;
                case 'ten_tests':
                    $earned = $stat->total_tests_taken >= 10;
                    break;
                case 'hundred_questions':
                    $earned = $stat->total_questions_attempted >= 100;
                    break;
                case 'perfect_score':
                    $earned = $stat->average_accuracy == 100 && $stat->total_tests_taken >= 1;
                    break;
                case 'high_achiever':
                    $earned = $stat->average_accuracy >= 80 && $stat->total_tests_taken >= 5;
                    break;
                case 'three_day_streak':
                    $earned = $streak && $streak->longest_streak >= 3;
                    break;
                case 'seven_day_streak':
                    $earned = $streak && $streak->longest_streak >= 7;
                    break;
                case 'thirty_day_streak':
                    $earned = $streak && $streak->longest_streak >= 30;
                    break;
                case 'points_100':
                    $earned = $stat->total_points >= 100;
                    break;
                case 'points_500':
                    $earned = $stat->total_points >= 500;
                    break;
                case 'points_1000':
                    $earned = $stat->total_points >= 1000;
                    break;
            }

            if ($earned) {
                UserBadge::create([
                    'user_id' => $user->id,
                    'badge_id' => $badge->id,
                    'earned_at' => now(),
                ]);
                $newBadges[] = $badge;
            }
        }

        return $newBadges;
    }

    public function getUserStats(User $user)
    {
        $stat = UserStat::where('user_id', $user->id)->first();
        $streak = UserStreak::where('user_id', $user->id)->first();
        $badges = UserBadge::with('badge')->where('user_id', $user->id)->get();

        return [
            'stats' => $stat ?? [
                'total_tests_taken' => 0,
                'total_questions_attempted' => 0,
                'total_correct_answers' => 0,
                'average_accuracy' => 0,
                'total_points' => 0,
            ],
            'streak' => $streak ?? [
                'current_streak' => 0,
                'longest_streak' => 0,
            ],
            'badges' => $badges->map(fn($ub) => [
                'id' => $ub->badge->id,
                'name' => $ub->badge->name,
                'description' => $ub->badge->description,
                'icon' => $ub->badge->icon,
                'color' => $ub->badge->color,
                'earned_at' => $ub->earned_at,
            ]),
        ];
    }
}
