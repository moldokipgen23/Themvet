@extends('layouts.admin')

@section('title', 'Edit Exam - ThemVet Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Edit Exam</h2>
    <a href="{{ route('admin.exams.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back to Exams
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ $exam->name }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.exams.update', $exam->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Exam Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $exam->name) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $exam->description) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-select">
                            <option value="1" {{ $exam->is_active ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ !$exam->is_active ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Exam
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Subjects</h5>
            </div>
            <div class="card-body">
                @if($exam->subjects->count() > 0)
                    <ul class="list-group">
                        @foreach($exam->subjects as $subject)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $subject->name }}
                                <span class="badge bg-secondary">{{ $subject->topics->count() }} topics</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted mb-0">No subjects yet.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
