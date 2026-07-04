<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MockTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'exam_pattern_id',
        'title',
        'description',
        'duration_minutes',
        'total_marks',
        'total_questions',
        'difficulty',
        'negative_marking',
        'negative_marking_value',
        'is_official',
        'created_by',
        'status',
    ];

    protected $casts = [
        'negative_marking' => 'boolean',
        'is_official' => 'boolean',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions()
    {
        return $this->hasMany(MockTestQuestion::class)->orderBy('order');
    }

    public function attempts()
    {
        return $this->hasMany(Attempt::class);
    }

    public function approvedQuestions()
    {
        return $this->belongsToMany(Question::class, 'mock_test_questions')
            ->withPivot('marks', 'negative_marks', 'order', 'mock_test_section_id')
            ->withTimestamps();
    }

    public function sections()
    {
        return $this->hasMany(MockTestSection::class)->orderBy('order');
    }

    public function examPattern()
    {
        return $this->belongsTo(ExamPattern::class);
    }
}
