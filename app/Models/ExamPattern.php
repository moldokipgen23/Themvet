<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ExamPattern extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'name',
        'slug',
        'description',
        'duration_minutes',
        'total_marks',
        'total_questions',
        'sections_count',
        'negative_marking',
        'negative_marking_value',
        'is_official',
        'is_active',
        'order',
        'details',
    ];

    protected $casts = [
        'negative_marking' => 'boolean',
        'is_official' => 'boolean',
        'is_active' => 'boolean',
        'details' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (ExamPattern $pattern) {
            if (empty($pattern->slug)) {
                $pattern->slug = Str::slug($pattern->name);
            }
        });
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function sections()
    {
        return $this->hasMany(ExamSection::class)->orderBy('order');
    }

    public function mockTests()
    {
        return $this->hasMany(MockTest::class);
    }
}
