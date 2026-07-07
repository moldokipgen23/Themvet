<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_tests_taken',
        'total_questions_attempted',
        'total_correct_answers',
        'average_accuracy',
        'total_points',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function recordTestAttempt($totalQuestions, $correctAnswers, $pointsOverride = null)
    {
        $this->increment('total_tests_taken');
        $this->increment('total_questions_attempted', $totalQuestions);
        $this->increment('total_correct_answers', $correctAnswers);

        $accuracy = $this->total_questions_attempted > 0
            ? ($this->total_correct_answers / $this->total_questions_attempted) * 100
            : 0;

        $points = $pointsOverride ?? ($correctAnswers * 10);

        $this->update([
            'average_accuracy' => round($accuracy, 2),
            'total_points' => $this->total_points + $points,
        ]);

        return $points;
    }
}
