<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ExamSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_pattern_id',
        'name',
        'slug',
        'subject_id',
        'total_questions',
        'total_marks',
        'duration_minutes',
        'marks_per_question',
        'negative_marks_per_question',
        'difficulty_range',
        'is_mandatory',
        'order',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (ExamSection $section) {
            if (empty($section->slug)) {
                $section->slug = Str::slug($section->name);
            }
        });
    }

    public function examPattern()
    {
        return $this->belongsTo(ExamPattern::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function mockTestSections()
    {
        return $this->hasMany(MockTestSection::class);
    }
}
