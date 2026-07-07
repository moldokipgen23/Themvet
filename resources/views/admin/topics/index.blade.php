@extends('layouts.admin')

@section('title', 'Manage Topics - ThemVet Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-tags"></i> Topics</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTopicModal">
        <i class="fas fa-plus"></i> Add Topic
    </button>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}</div>
@endif

<div class="card">
    <div class="card-body">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Subject</th>
                    <th>Exam</th>
                    <th>Questions</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topics as $topic)
                <tr>
                    <td>{{ $topic->id }}</td>
                    <td>{{ $topic->name }}</td>
                    <td><span class="badge bg-info">{{ $topic->subject->name }}</span></td>
                    <td><span class="badge bg-primary">{{ $topic->subject->exam->name }}</span></td>
                    <td><span class="badge bg-secondary">{{ $topic->questions->count() }}</span></td>
                    <td>
                        @if($topic->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-warning">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editTopicModal{{ $topic->id }}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form action="{{ route('admin.topics.destroy', $topic->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this topic?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>

                <!-- Edit Modal -->
                <div class="modal fade" id="editTopicModal{{ $topic->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <form action="{{ route('admin.topics.update', $topic->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Topic</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Name</label>
                                        <input type="text" name="name" class="form-control" value="{{ $topic->name }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Subject</label>
                                        <select name="subject_id" class="form-select" required>
                                            @foreach($subjects as $subject)
                                                <option value="{{ $subject->id }}" {{ $topic->subject_id == $subject->id ? 'selected' : '' }}>{{ $subject->exam->name }} - {{ $subject->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" class="form-control" rows="2">{{ $topic->description }}</textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select name="is_active" class="form-select">
                                            <option value="1" {{ $topic->is_active ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ !$topic->is_active ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Update</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                @empty
                <tr><td colspan="7" class="text-center text-muted">No topics found.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $topics->links() }}
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createTopicModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.topics.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Topic</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <select name="subject_id" class="form-select" required>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->exam->name }} - {{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
