@extends('layouts.admin')

@section('title', 'Dashboard - ThemVet')

@section('content')
<h2 class="mb-4">
    @if(Auth::user()->isAdmin())
        Admin Dashboard
    @elseif(Auth::user()->isTeacher())
        Teacher Dashboard
    @else
        My Dashboard
    @endif
</h2>

{{-- ADMIN STATS --}}
@if(Auth::user()->isAdmin())
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card primary">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted">Total Users</h6>
                        <h3>{{ $stats['total_users'] ?? 0 }}</h3>
                    </div>
                    <i class="fas fa-users fa-2x text-primary opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted">Total Questions</h6>
                        <h3>{{ $stats['total_questions'] ?? 0 }}</h3>
                    </div>
                    <i class="fas fa-question-circle fa-2x text-success opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted">Pending Review</h6>
                        <h3>{{ $stats['pending_questions'] ?? 0 }}</h3>
                    </div>
                    <i class="fas fa-clock fa-2x text-warning opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card danger">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted">Total Attempts</h6>
                        <h3>{{ $stats['total_attempts'] ?? 0 }}</h3>
                    </div>
                    <i class="fas fa-clipboard-check fa-2x text-danger opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Quick Actions</h5></div>
            <div class="card-body">
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary me-2 mb-2">
                    <i class="fas fa-users"></i> Manage Users
                </a>
                <a href="{{ route('admin.exams.index') }}" class="btn btn-outline-success me-2 mb-2">
                    <i class="fas fa-book"></i> Manage Exams
                </a>
                <a href="{{ route('admin.questions.index') }}" class="btn btn-outline-warning me-2 mb-2">
                    <i class="fas fa-question-circle"></i> Manage Questions
                </a>
                <a href="{{ route('admin.mock-tests.index') }}" class="btn btn-outline-info me-2 mb-2">
                    <i class="fas fa-clipboard-list"></i> Manage Mock Tests
                </a>
                <a href="{{ route('admin.analytics') }}" class="btn btn-outline-secondary me-2 mb-2">
                    <i class="fas fa-chart-bar"></i> Analytics
                </a>
                <a href="{{ route('admin.settings') }}" class="btn btn-outline-dark me-2 mb-2">
                    <i class="fas fa-cog"></i> Settings
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Recent Activity</h5></div>
            <div class="card-body">
                <p class="text-muted">No recent activity to display.</p>
            </div>
        </div>
    </div>
</div>

{{-- TEACHER STATS --}}
@elseif(Auth::user()->isTeacher())
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card primary">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted">My Questions</h6>
                        <h3>{{ $stats['my_questions'] ?? 0 }}</h3>
                    </div>
                    <i class="fas fa-question-circle fa-2x text-primary opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted">Approved</h6>
                        <h3>{{ $stats['approved_questions'] ?? 0 }}</h3>
                    </div>
                    <i class="fas fa-check-circle fa-2x text-success opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted">Pending Review</h6>
                        <h3>{{ $stats['pending_questions'] ?? 0 }}</h3>
                    </div>
                    <i class="fas fa-clock fa-2x text-warning opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card info">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted">Mock Tests</h6>
                        <h3>{{ $stats['total_mock_tests'] ?? 0 }}</h3>
                    </div>
                    <i class="fas fa-clipboard-list fa-2x text-info opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Quick Actions</h5></div>
            <div class="card-body">
                <a href="{{ route('admin.questions.index') }}" class="btn btn-outline-primary me-2 mb-2">
                    <i class="fas fa-question-circle"></i> My Questions
                </a>
                <a href="{{ route('admin.review-queue') }}" class="btn btn-outline-warning me-2 mb-2">
                    <i class="fas fa-clipboard-check"></i> Review Queue
                </a>
                <a href="{{ route('admin.mock-tests.index') }}" class="btn btn-outline-info me-2 mb-2">
                    <i class="fas fa-clipboard-list"></i> Mock Tests
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Recent Activity</h5></div>
            <div class="card-body">
                <p class="text-muted">No recent activity to display.</p>
            </div>
        </div>
    </div>
</div>

{{-- STUDENT STATS --}}
@else
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card primary">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted">Available Exams</h6>
                        <h3>{{ $stats['total_exams'] ?? 0 }}</h3>
                    </div>
                    <i class="fas fa-book fa-2x text-primary opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted">Mock Tests</h6>
                        <h3>{{ $stats['total_mock_tests'] ?? 0 }}</h3>
                    </div>
                    <i class="fas fa-clipboard-list fa-2x text-success opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted">Practice Questions</h6>
                        <h3>{{ $stats['total_questions'] ?? 0 }}</h3>
                    </div>
                    <i class="fas fa-question-circle fa-2x text-warning opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card danger">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted">My Attempts</h6>
                        <h3>{{ $stats['my_attempts'] ?? 0 }}</h3>
                    </div>
                    <i class="fas fa-clipboard-check fa-2x text-danger opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Quick Actions</h5></div>
            <div class="card-body">
                <a href="{{ route('admin.exams.index') }}" class="btn btn-outline-primary me-2 mb-2">
                    <i class="fas fa-book"></i> Browse Exams
                </a>
                <a href="{{ route('admin.mock-tests.index') }}" class="btn btn-outline-success me-2 mb-2">
                    <i class="fas fa-clipboard-list"></i> Take Mock Test
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Recent Activity</h5></div>
            <div class="card-body">
                <p class="text-muted">No recent activity to display.</p>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
