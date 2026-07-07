<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserStreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'current_streak',
        'longest_streak',
        'last_activity_date',
    ];

    protected function casts(): array
    {
        return [
            'last_activity_date' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function recordActivity()
    {
        $today = now()->startOfDay();
        $lastActivity = $this->last_activity_date?->startOfDay();

        if ($lastActivity === null) {
            $this->update([
                'current_streak' => 1,
                'last_activity_date' => $today,
            ]);
            return;
        }

        if ($lastActivity->isSameDay($today)) {
            return;
        }

        $diff = $lastActivity->diffInDays($today);

        if ($diff === 1) {
            $newStreak = $this->current_streak + 1;
            $this->update([
                'current_streak' => $newStreak,
                'longest_streak' => max($this->longest_streak, $newStreak),
                'last_activity_date' => $today,
            ]);
        } else {
            $this->update([
                'current_streak' => 1,
                'last_activity_date' => $today,
            ]);
        }
    }
}
