<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MockTestQuestion extends Model
{
    use HasFactory;

    protected $fillable = ['mock_test_id', 'question_id', 'marks', 'negative_marks', 'order', 'mock_test_section_id'];

    public function mockTest()
    {
        return $this->belongsTo(MockTest::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function section()
    {
        return $this->belongsTo(MockTestSection::class, 'mock_test_section_id');
    }
}
