<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function practice(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'subject_id' => 'required|exists:subjects,id',
            'topic_id' => 'sometimes|exists:topics,id',
            'difficulty' => 'sometimes|in:easy,medium,hard',
            'count' => 'sometimes|integer|min:1|max:50',
        ]);

        $query = Question::where('exam_id', $request->exam_id)
            ->where('subject_id', $request->subject_id)
            ->where('status', 'approved');

        if ($request->topic_id) {
            $query->where('topic_id', $request->topic_id);
        }

        if ($request->difficulty) {
            $query->where('difficulty', $request->difficulty);
        }

        $count = $request->get('count', 10);
        $questions = $query->with('options')
            ->inRandomOrder()
            ->limit($count)
            ->get();

        $questions->each(function ($q) {
            $q->options->each->makeHidden('is_correct');
            $q->makeHidden('correct_answer');
        });

        return response()->json([
            'status' => 'success',
            'data' => ['questions' => $questions],
        ]);
    }

    public function show($id)
    {
        $question = Question::where('status', 'approved')
            ->with('options')
            ->findOrFail($id);

        $question->options->each->makeHidden('is_correct');
        $question->makeHidden('correct_answer');

        return response()->json([
            'status' => 'success',
            'data' => ['question' => $question],
        ]);
    }
}
