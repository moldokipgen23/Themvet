<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'is_active', 'order'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Exam $exam) {
            if (empty($exam->slug)) {
                $exam->slug = Str::slug($exam->name);
            }
        });
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function mockTests()
    {
        return $this->hasMany(MockTest::class);
    }

    public function users()
    {
        return $this->hasMany(User::class, 'target_exam_id');
    }

    public function reviewerAssignments()
    {
        return $this->hasMany(ReviewerAssignment::class);
    }

    public function patterns()
    {
        return $this->hasMany(ExamPattern::class)->orderBy('order');
    }

    public function officialPatterns()
    {
        return $this->patterns()->where('is_official', true);
    }
}
