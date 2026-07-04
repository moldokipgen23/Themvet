<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'mock_test_id',
        'user_id',
        'started_at',
        'submitted_at',
        'score',
        'total_marks',
        'accuracy',
        'time_spent_seconds',
        'status',
        'current_section_id',
        'section_time_remaining',
        'section_status',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'score' => 'decimal:2',
            'accuracy' => 'decimal:2',
            'section_time_remaining' => 'array',
            'section_status' => 'array',
        ];
    }

    public function mockTest()
    {
        return $this->belongsTo(MockTest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function answers()
    {
        return $this->hasMany(AttemptAnswer::class);
    }
}
