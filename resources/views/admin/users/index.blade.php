@extends('layouts.admin')

@section('title', 'Users - ThemVet Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Users</h2>
    <span class="badge bg-primary">{{ $users->total() }} users</span>
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
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Reviewer Assignments</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @foreach($user->roles as $role)
                                <span class="badge bg-secondary">{{ $role->name }}</span>
                            @endforeach
                        </td>
                        <td>
                            @if($user->isTeacher() && $user->reviewerAssignments->count() > 0)
                                @foreach($user->reviewerAssignments->take(3) as $assignment)
                                    <span class="badge bg-light text-dark border">
                                        {{ $assignment->exam->name ?? 'N/A' }}
                                        @if($assignment->subject)
                                            / {{ $assignment->subject->name }}
                                        @endif
                                    </span>
                                @endforeach
                                @if($user->reviewerAssignments->count() > 3)
                                    <span class="badge bg-light text-muted">+{{ $user->reviewerAssignments->count() - 3 }} more</span>
                                @endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($user->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td>{{ $user->created_at->format('M d, Y') }}</td>
                        <td>
                            <form action="{{ route('admin.users.role', $user) }}" method="POST" class="d-inline">
                                @csrf
                                <select name="role" class="form-select form-select-sm" style="width: auto; display: inline-block;" onchange="this.form.submit()">
                                    <option value="student" {{ $user->hasRole('student') ? 'selected' : '' }}>Student</option>
                                    <option value="teacher" {{ $user->hasRole('teacher') ? 'selected' : '' }}>Teacher</option>
                                    <option value="admin" {{ $user->hasRole('admin') ? 'selected' : '' }}>Admin</option>
                                </select>
                            </form>

                            @if($user->isTeacher())
                                <button type="button" class="btn btn-sm btn-outline-primary ms-1"
                                        data-bs-toggle="modal" data-bs-target="#assignModal{{ $user->id }}">
                                    <i class="fas fa-link"></i> Assign
                                </button>
                            @endif
                        </td>
                    </tr>

                    <!-- Assign Reviewer Modal -->
                    @if($user->isTeacher())
                    <div class="modal fade" id="assignModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('admin.reviewer-assignments.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Assign Reviewer: {{ $user->name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="exam_id_{{ $user->id }}" class="form-label">Exam <span class="text-danger">*</span></label>
                                            <select name="exam_id" id="exam_id_{{ $user->id }}" class="form-select" required>
                                                <option value="">Select Exam</option>
                                                @foreach($exams as $exam)
                                                    <option value="{{ $exam->id }}">{{ $exam->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="subject_id_{{ $user->id }}" class="form-label">Subject</label>
                                            <select name="subject_id" id="subject_id_{{ $user->id }}" class="form-select">
                                                <option value="">All Subjects</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="level_{{ $user->id }}" class="form-label">Level <span class="text-danger">*</span></label>
                                            <select name="level" id="level_{{ $user->id }}" class="form-select" required>
                                                <option value="reviewer">Reviewer</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Assign</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endif
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">No users found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $users->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[id^="exam_id_"]').forEach(select => {
    select.addEventListener('change', function() {
        const examId = this.value;
        const modal = this.closest('.modal-content');
        const subjectSelect = modal.querySelector('[id^="subject_id_"]');
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
});
</script>
@endpush
