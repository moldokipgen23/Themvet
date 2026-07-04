@extends('layouts.admin')

@section('title', 'Review Queue - ThemVet Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Review Queue</h2>
    <span class="badge bg-warning">{{ $questions->total() }} pending</span>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Question</th>
                        <th>Exam</th>
                        <th>Subject</th>
                        <th>Difficulty</th>
                        <th>Teacher</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($questions as $question)
                    <tr>
                        <td>{{ $question->id }}</td>
                        <td title="{{ $question->question_text }}">
                            {{ Str::limit(strip_tags($question->question_text), 80) }}
                        </td>
                        <td>{{ $question->exam->name ?? 'N/A' }}</td>
                        <td>{{ $question->subject->name ?? 'N/A' }}</td>
                        <td>
                            @switch($question->difficulty)
                                @case('easy')
                                    <span class="badge bg-success">Easy</span>
                                    @break
                                @case('medium')
                                    <span class="badge bg-warning text-dark">Medium</span>
                                    @break
                                @case('hard')
                                    <span class="badge bg-danger">Hard</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary">{{ ucfirst($question->difficulty) }}</span>
                            @endswitch
                        </td>
                        <td>{{ $question->contributor->name ?? 'N/A' }}</td>
                        <td>{{ $question->created_at->format('M d, Y') }}</td>
                        <td>
                            <form action="{{ route('admin.questions.approve', $question->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-success" title="Approve">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>

                            <button type="button" class="btn btn-sm btn-outline-danger" title="Reject"
                                    data-bs-toggle="modal" data-bs-target="#rejectModal{{ $question->id }}">
                                <i class="fas fa-times"></i>
                            </button>

                            <a href="{{ route('admin.questions.index') }}?status=pending&id={{ $question->id }}" class="btn btn-sm btn-outline-info" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>

                    <!-- Reject Modal for this question -->
                    <div class="modal fade" id="rejectModal{{ $question->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('admin.questions.reject', $question->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title">Reject Question #{{ $question->id }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p class="text-muted mb-3">{{ Str::limit(strip_tags($question->question_text), 120) }}</p>
                                        <div class="mb-3">
                                            <label for="reason" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                                            <textarea name="reason" id="reason" class="form-control" rows="3" required
                                                      placeholder="Explain why this question is being rejected..."></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger">Reject Question</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">No pending questions in the review queue.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $questions->links() }}
    </div>
</div>
@endsection
