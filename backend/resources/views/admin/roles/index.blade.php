@extends('layouts.admin')

@section('title', 'Roles & Permissions - ThemVet Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-user-shield"></i> Roles & Permissions</h2>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}</div>
@endif

@foreach(['system' => ['label' => 'System Users', 'icon' => 'fa-server', 'color' => 'danger', 'desc' => 'Platform management, oversight, and moderation. These users access the admin panel, not the Flutter app.'],
           'teacher' => ['label' => 'Teachers & Reviewers', 'icon' => 'fa-chalkboard-teacher', 'color' => 'success', 'desc' => 'Content creation, review, and quality control. These users have special tabs in the Flutter app.'],
           'student' => ['label' => 'Students', 'icon' => 'fa-user-graduate', 'color' => 'primary', 'desc' => 'App consumers who take mock tests, practice questions, and compete on leaderboards.']] as $group => $info)
<div class="card mb-4">
    <div class="card-header d-flex align-items-center gap-2">
        <i class="fas {{ $info['icon'] }} text-{{ $info['color'] }}"></i>
        <h5 class="mb-0">{{ $info['label'] }}</h5>
    </div>
    <div class="card-body">
        <p class="text-muted mb-3">{{ $info['desc'] }}</p>
        <div class="row">
            @foreach($roles->where('group', $group) as $role)
            <div class="col-md-4 mb-3">
                <div class="card h-100 border-{{ $info['color'] }}">
                    <div class="card-header d-flex justify-content-between align-items-center bg-{{ $info['color'] }} bg-opacity-10">
                        <h6 class="mb-0">
                            @switch($role->name)
                                @case('admin')
                                    <i class="fas fa-crown text-danger"></i>
                                    @break
                                @case('moderator')
                                    <i class="fas fa-shield-alt text-warning"></i>
                                    @break
                                @case('lead_reviewer')
                                    <i class="fas fa-star text-warning"></i>
                                    @break
                                @case('reviewer')
                                    <i class="fas fa-check-circle text-info"></i>
                                    @break
                                @case('teacher')
                                    <i class="fas fa-chalkboard-teacher text-success"></i>
                                    @break
                                @default
                                    <i class="fas fa-user-graduate text-primary"></i>
                            @endswitch
                            {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                        </h6>
                        <span class="badge bg-secondary">{{ $role->users_count }} users</span>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">{{ $role->description ?? 'No description' }}</p>
                        <h6>Permissions:</h6>
                        <ul class="list-unstyled mb-0">
                            @switch($role->name)
                                @case('admin')
                                    <li><i class="fas fa-check text-success"></i> Full platform management</li>
                                    <li><i class="fas fa-check text-success"></i> User & role management</li>
                                    <li><i class="fas fa-check text-success"></i> Exam & content structure</li>
                                    <li><i class="fas fa-check text-success"></i> Platform settings</li>
                                    <li><i class="fas fa-check text-success"></i> Analytics & reports</li>
                                    @break
                                @case('moderator')
                                    <li><i class="fas fa-check text-success"></i> Content moderation</li>
                                    <li><i class="fas fa-check text-success"></i> User reports management</li>
                                    <li><i class="fas fa-check text-success"></i> Review queue access</li>
                                    <li><i class="fas fa-times text-danger"></i> Cannot change system settings</li>
                                    <li><i class="fas fa-times text-danger"></i> Cannot manage roles</li>
                                    @break
                                @case('lead_reviewer')
                                    <li><i class="fas fa-check text-success"></i> All reviewer features</li>
                                    <li><i class="fas fa-check text-success"></i> Override reviewer decisions</li>
                                    <li><i class="fas fa-check text-success"></i> Official test creation</li>
                                    <li><i class="fas fa-check text-success"></i> Quality control</li>
                                    @break
                                @case('reviewer')
                                    <li><i class="fas fa-check text-success"></i> Review queue access</li>
                                    <li><i class="fas fa-check text-success"></i> Approve/reject questions</li>
                                    <li><i class="fas fa-check text-success"></i> Create official tests</li>
                                    <li><i class="fas fa-check text-success"></i> Edit pending questions</li>
                                    @break
                                @case('teacher')
                                    <li><i class="fas fa-check text-success"></i> Create questions</li>
                                    <li><i class="fas fa-check text-success"></i> Create test drafts</li>
                                    <li><i class="fas fa-check text-success"></i> View contribution status</li>
                                    <li><i class="fas fa-times text-danger"></i> Cannot publish directly</li>
                                    @break
                                @default
                                    <li><i class="fas fa-check text-success"></i> Practice questions</li>
                                    <li><i class="fas fa-check text-success"></i> Take mock tests</li>
                                    <li><i class="fas fa-check text-success"></i> View results & analytics</li>
                                    <li><i class="fas fa-check text-success"></i> Leaderboard & achievements</li>
                                    <li><i class="fas fa-times text-danger"></i> Cannot create content</li>
                            @endswitch
                        </ul>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endforeach

<div class="card mt-4">
    <div class="card-header">
        <h5><i class="fas fa-info-circle"></i> Role Groups</h5>
    </div>
    <div class="card-body">
        <div class="row text-center">
            <div class="col-md-4">
                <div class="p-3 border rounded mb-3">
                    <i class="fas fa-server fa-2x text-danger mb-2"></i>
                    <h6>System</h6>
                    <small class="text-muted">Admin panel only. Manage platform, users, settings.</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 border rounded mb-3">
                    <i class="fas fa-chalkboard-teacher fa-2x text-success mb-2"></i>
                    <h6>Teacher</h6>
                    <small class="text-muted">Flutter app with teacher tabs. Create & review content.</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 border rounded mb-3">
                    <i class="fas fa-user-graduate fa-2x text-primary mb-2"></i>
                    <h6>Student</h6>
                    <small class="text-muted">Flutter app. Take tests, practice, compete.</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
