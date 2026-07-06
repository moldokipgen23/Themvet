@extends('layouts.admin')

@section('title', 'Roles & Permissions - ThemVet Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-user-shield"></i> Roles & Permissions</h2>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}</div>
@endif

<div class="row">
    @foreach($roles as $role)
    <div class="col-md-4 mb-4">
        <div class="card h-100 border-{{ $role->name === 'admin' ? 'danger' : ($role->name === 'teacher' ? 'success' : 'primary') }}">
            <div class="card-header d-flex justify-content-between align-items-center bg-{{ $role->name === 'admin' ? 'danger' : ($role->name === 'teacher' ? 'success' : 'primary') }} bg-opacity-10">
                <h6 class="mb-0">
                    @if($role->name === 'admin')
                        <i class="fas fa-crown text-danger"></i>
                    @elseif($role->name === 'teacher')
                        <i class="fas fa-chalkboard-teacher text-success"></i>
                    @else
                        <i class="fas fa-user-graduate text-primary"></i>
                    @endif
                    {{ ucfirst($role->name) }}
                </h6>
                <span class="badge bg-secondary">{{ $role->users_count }} users</span>
            </div>
            <div class="card-body">
                <p class="text-muted small">{{ $role->description ?? 'No description' }}</p>
                <h6>Permissions:</h6>
                <ul class="list-unstyled mb-0">
                    @if($role->name === 'admin')
                        <li><i class="fas fa-check text-success"></i> Full platform management</li>
                        <li><i class="fas fa-check text-success"></i> User & role management</li>
                        <li><i class="fas fa-check text-success"></i> Exam & content structure</li>
                        <li><i class="fas fa-check text-success"></i> Platform settings</li>
                        <li><i class="fas fa-check text-success"></i> Analytics & reports</li>
                    @elseif($role->name === 'teacher')
                        <li><i class="fas fa-check text-success"></i> Create questions</li>
                        <li><i class="fas fa-check text-success"></i> Review & approve questions</li>
                        <li><i class="fas fa-check text-success"></i> Create & publish mock tests</li>
                        <li><i class="fas fa-check text-success"></i> View contribution stats</li>
                    @else
                        <li><i class="fas fa-check text-success"></i> Practice questions</li>
                        <li><i class="fas fa-check text-success"></i> Take mock tests</li>
                        <li><i class="fas fa-check text-success"></i> View results & analytics</li>
                        <li><i class="fas fa-check text-success"></i> Leaderboard & achievements</li>
                        <li><i class="fas fa-times text-danger"></i> Cannot create content</li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="card mt-4">
    <div class="card-header">
        <h5><i class="fas fa-info-circle"></i> Role Summary</h5>
    </div>
    <div class="card-body">
        <div class="row text-center">
            <div class="col-md-4">
                <div class="p-3 border rounded mb-3">
                    <i class="fas fa-crown fa-2x text-danger mb-2"></i>
                    <h6>Admin</h6>
                    <small class="text-muted">Full platform control. Manage users, exams, settings.</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 border rounded mb-3">
                    <i class="fas fa-chalkboard-teacher fa-2x text-success mb-2"></i>
                    <h6>Teacher</h6>
                    <small class="text-muted">Create content, review questions, publish tests.</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 border rounded mb-3">
                    <i class="fas fa-user-graduate fa-2x text-primary mb-2"></i>
                    <h6>Student</h6>
                    <small class="text-muted">Take tests, practice, compete on leaderboards.</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
