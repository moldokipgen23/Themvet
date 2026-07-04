<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MockTestSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'mock_test_id',
        'exam_section_id',
        'name',
        'total_questions',
        'total_marks',
        'duration_minutes',
        'marks_per_question',
        'negative_marks_per_question',
        'is_mandatory',
        'order',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
    ];

    public function mockTest()
    {
        return $this->belongsTo(MockTest::class);
    }

    public function examSection()
    {
        return $this->belongsTo(ExamSection::class);
    }

    public function questions()
    {
        return $this->hasMany(MockTestQuestion::class, 'mock_test_section_id')->orderBy('order');
    }
}
