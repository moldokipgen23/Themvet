<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'subject_id',
        'topic_id',
        'question_text',
        'question_type',
        'difficulty',
        'explanation',
        'correct_answer',
        'contributor_id',
        'reviewer_id',
        'status',
        'tags',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function contributor()
    {
        return $this->belongsTo(User::class, 'contributor_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function options()
    {
        return $this->hasMany(QuestionOption::class)->orderBy('order');
    }

    public function reviews()
    {
        return $this->hasMany(QuestionReview::class);
    }

    public function mockTestQuestions()
    {
        return $this->hasMany(MockTestQuestion::class);
    }

    public function attemptAnswers()
    {
        return $this->hasMany(AttemptAnswer::class);
    }

    public function correctOptions()
    {
        return $this->options()->where('is_correct', true);
    }
}
