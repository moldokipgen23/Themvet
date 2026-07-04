<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamPattern;
use App\Models\Subject;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index()
    {
        $exams = Exam::where('is_active', true)
            ->with(['subjects' => function ($query) {
                $query->where('is_active', true)->with('topics');
            }, 'patterns' => function ($query) {
                $query->where('is_active', true)->with('sections');
            }])
            ->orderBy('order')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => ['exams' => $exams],
        ]);
    }

    public function show($id)
    {
        $exam = Exam::where('is_active', true)
            ->with(['subjects' => function ($query) {
                $query->where('is_active', true)->with('topics');
            }, 'patterns' => function ($query) {
                $query->where('is_active', true)->with('sections');
            }])
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => ['exam' => $exam],
        ]);
    }

    public function subjects($examId)
    {
        $exam = Exam::findOrFail($examId);

        $subjects = $exam->subjects()
            ->where('is_active', true)
            ->with('topics')
            ->orderBy('order')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => ['subjects' => $subjects],
        ]);
    }

    public function topics($examId, $subjectId)
    {
        $subject = Subject::where('exam_id', $examId)->where('id', $subjectId)->firstOrFail();

        $topics = $subject->topics()
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => ['topics' => $topics],
        ]);
    }

    public function subjectTopics($subjectId)
    {
        $subject = Subject::with('exam')->findOrFail($subjectId);

        $topics = $subject->topics()
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'subject' => $subject,
                'topics' => $topics,
            ],
        ]);
    }

    public function pattern($id)
    {
        $pattern = ExamPattern::with(['exam', 'sections' => function ($query) {
            $query->with('subject');
        }])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => ['pattern' => $pattern],
        ]);
    }
}
