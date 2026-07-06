<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Question;

class QuestionPolicy
{
    public function viewAny(User $user, ?Question $question = null)
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isTeacher()) {
            return true;
        }

        if ($user->isReviewer()) {
            return true;
        }

        return false;
    }

    public function view(User $user, Question $question)
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isTeacher() && $question->contributor_id === $user->id) {
            return true;
        }

        if ($user->isReviewer()) {
            return $user->reviewerAssignments()
                ->active()
                ->where('exam_id', $question->exam_id)
                ->where(function ($q) use ($question) {
                    $q->where('subject_id', $question->subject_id)
                      ->orWhereNull('subject_id');
                })
                ->exists();
        }

        return false;
    }

    public function create(User $user)
    {
        return $user->isTeacher();
    }

    public function update(User $user, Question $question)
    {
        if (! $user->isTeacher()) {
            return false;
        }

        if ($question->contributor_id !== $user->id) {
            return false;
        }

        return in_array($question->status, ['draft', 'pending', 'rejected']);
    }

    public function delete(User $user, Question $question)
    {
        if (! $user->isTeacher()) {
            return false;
        }

        if ($question->contributor_id !== $user->id) {
            return false;
        }

        return $question->status === 'draft';
    }

    public function approve(User $user, Question $question)
    {
        if (! $user->isReviewer()) {
            return false;
        }

        return $user->reviewerAssignments()
            ->active()
            ->where('exam_id', $question->exam_id)
            ->where(function ($q) use ($question) {
                $q->where('subject_id', $question->subject_id)
                  ->orWhereNull('subject_id');
            })
            ->exists();
    }

    public function reject(User $user, Question $question)
    {
        if (! $user->isReviewer()) {
            return false;
        }

        return $user->reviewerAssignments()
            ->active()
            ->where('exam_id', $question->exam_id)
            ->where(function ($q) use ($question) {
                $q->where('subject_id', $question->subject_id)
                  ->orWhereNull('subject_id');
            })
            ->exists();
    }
}
