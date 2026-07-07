@extends('layouts.admin')

@section('title', 'Reviewer Assignments - ThemVet Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Reviewer Assignments</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAssignmentModal">
        <i class="fas fa-plus"></i> New Assignment
    </button>
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
                        <th>Reviewer</th>
                        <th>Exam</th>
                        <th>Subject</th>
                        <th>Level</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $assignment)
                    <tr>
                        <td>{{ $assignment->id }}</td>
                        <td>{{ $assignment->user->name ?? 'N/A' }}</td>
                        <td>{{ $assignment->exam->name ?? 'N/A' }}</td>
                        <td>{{ $assignment->subject->name ?? 'All Subjects' }}</td>
                        <td>
                            <span class="badge bg-primary">
                                {{ ucfirst($assignment->level) }}
                            </span>
                        </td>
                        <td>
                            @if($assignment->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('admin.reviewer-assignments.destroy', $assignment->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Are you sure you want to remove this assignment?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">No reviewer assignments found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $assignments->links() }}
    </div>
</div>

<!-- Create Assignment Modal -->
<div class="modal fade" id="createAssignmentModal" tabindex="-1" aria-labelledby="createAssignmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.reviewer-assignments.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createAssignmentModalLabel">Assign Reviewer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Reviewer <span class="text-danger">*</span></label>
                        <select name="user_id" id="user_id" class="form-select" required>
                            <option value="">Select Reviewer</option>
                            @foreach(\App\Models\User::whereHas('roles', function($q) { $q->where('name', 'teacher'); })->get() as $reviewer)
                                <option value="{{ $reviewer->id }}">{{ $reviewer->name }} ({{ $reviewer->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="exam_id" class="form-label">Exam <span class="text-danger">*</span></label>
                        <select name="exam_id" id="exam_id" class="form-select" required>
                            <option value="">Select Exam</option>
                            @foreach(\App\Models\Exam::where('is_active', true)->get() as $exam)
                                <option value="{{ $exam->id }}">{{ $exam->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="subject_id" class="form-label">Subject</label>
                        <select name="subject_id" id="subject_id" class="form-select">
                            <option value="">All Subjects</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="level" class="form-label">Level <span class="text-danger">*</span></label>
                        <select name="level" id="level" class="form-select" required>
                            <option value="reviewer">Reviewer</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Assignment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('exam_id').addEventListener('change', function() {
    const examId = this.value;
    const subjectSelect = document.getElementById('subject_id');
    subjectSelect.innerHTML = '<option value="">Loading...</option>';

    if (!examId) {
        subjectSelect.innerHTML = '<option value="">All Subjects</option>';
        return;
    }

    fetch(`/admin/exams/${examId}/subjects`)
        .then(response => response.json())
        .then(data => {
            subjectSelect.innerHTML = '<option value="">All Subjects</option>';
            data.forEach(subject => {
                subjectSelect.innerHTML += `<option value="${subject.id}">${subject.name}</option>`;
            });
        })
        .catch(() => {
            subjectSelect.innerHTML = '<option value="">All Subjects</option>';
        });
});
</script>
@endpush
