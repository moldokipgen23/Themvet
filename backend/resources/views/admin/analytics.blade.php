@extends('layouts.admin')

@section('title', 'Analytics - ThemVet Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Analytics</h2>
</div>

<!-- Overview Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card stat-card primary">
            <div class="card-body">
                <h5 class="card-title text-muted">Total Users</h5>
                <h2 class="mb-0">{{ $totalUsers }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card success">
            <div class="card-body">
                <h5 class="card-title text-muted">Total Attempts</h5>
                <h2 class="mb-0">{{ $totalAttempts }}</h2>
                <small class="text-muted">{{ $todayAttempts }} today</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card warning">
            <div class="card-body">
                <h5 class="card-title text-muted">Pending Review</h5>
                <h2 class="mb-0">{{ $pendingQuestions }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card" style="border-left-color: #6f42c1;">
            <div class="card-body">
                <h5 class="card-title text-muted">Published Tests</h5>
                <h2 class="mb-0">{{ $publishedTests }} / {{ $totalMockTests }}</h2>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Users by Role -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Users by Role</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th>Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($usersByRole as $role)
                            <tr>
                                <td>{{ ucfirst($role->name) }}</td>
                                <td>{{ $role->users_count }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Questions by Status -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Questions by Status</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Total</td><td>{{ $questionsByStatus['total'] }}</td></tr>
                            <tr><td>Approved</td><td>{{ $questionsByStatus['approved'] }}</td></tr>
                            <tr><td>Pending</td><td>{{ $questionsByStatus['pending'] }}</td></tr>
                            <tr><td>Rejected</td><td>{{ $questionsByStatus['rejected'] }}</td></tr>
                            <tr><td>Draft</td><td>{{ $questionsByStatus['draft'] }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Daily Attempts (Last 14 Days) -->
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Daily Test Attempts (Last 14 Days)</h5>
            </div>
            <div class="card-body">
                @if($attemptsByDay->count() > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Attempts</th>
                                <th>Trend</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attemptsByDay as $day)
                            <tr>
                                <td>{{ $day->date }}</td>
                                <td>{{ $day->count }}</td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar" role="progressbar"
                                            style="width: {{ ($day->count / $attemptsByDay->max('count')) * 100 }}%;"
                                            aria-valuenow="{{ $day->count }}"
                                            aria-valuemin="0"
                                            aria-valuemax="{{ $attemptsByDay->max('count') }}">
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted mb-0">No attempts in the last 14 days.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Top Exams -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Top Exams</h5>
            </div>
            <div class="card-body">
                @if($topExams->count() > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Exam</th>
                                <th>Tests</th>
                                <th>Questions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topExams as $exam)
                            <tr>
                                <td>{{ $exam->name }}</td>
                                <td>{{ $exam->mock_tests_count }}</td>
                                <td>{{ $exam->questions_count }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted mb-0">No exams yet.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Quick Stats -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Stats</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3 mb-3">
                        <h3>{{ $weekAttempts }}</h3>
                        <small class="text-muted">Attempts this week</small>
                    </div>
                    <div class="col-md-3 mb-3">
                        <h3>{{ $totalQuestions }}</h3>
                        <small class="text-muted">Total Questions</small>
                    </div>
                    <div class="col-md-3 mb-3">
                        <h3>{{ $totalMockTests }}</h3>
                        <small class="text-muted">Total Mock Tests</small>
                    </div>
                    <div class="col-md-3 mb-3">
                        <h3>{{ $totalUsers }}</h3>
                        <small class="text-muted">Total Users</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
