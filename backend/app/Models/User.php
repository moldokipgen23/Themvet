<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'target_exam_id',
        'is_active',
        'google_id',
        'avatar',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function targetExam()
    {
        return $this->belongsTo(Exam::class, 'target_exam_id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'contributor_id');
    }

    public function reviewedQuestions()
    {
        return $this->hasMany(Question::class, 'reviewer_id');
    }

    public function attempts()
    {
        return $this->hasMany(Attempt::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function reviewerAssignments()
    {
        return $this->hasMany(ReviewerAssignment::class);
    }

    public function assignedExams()
    {
        return $this->belongsToMany(Exam::class, 'reviewer_assignments')
            ->withPivot('subject_id', 'level', 'is_active');
    }

    public function createdMockTests()
    {
        return $this->hasMany(MockTest::class, 'created_by');
    }

    public function hasRole($roleName)
    {
        return $this->roles->contains('name', $roleName);
    }

    public function hasGroup($group)
    {
        return $this->roles->contains('group', $group);
    }

    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    public function isModerator()
    {
        return $this->hasRole('moderator');
    }

    public function isSystemUser()
    {
        return $this->hasGroup('system');
    }

    public function isTeacher()
    {
        return $this->hasGroup('teacher');
    }

    public function isContributor()
    {
        return $this->isTeacher();
    }

    public function isReviewer()
    {
        return $this->hasRole('reviewer') || $this->hasRole('lead_reviewer');
    }

    public function isLeadReviewer()
    {
        return $this->hasRole('lead_reviewer');
    }

    public function isStudent()
    {
        return $this->hasGroup('student');
    }
}
