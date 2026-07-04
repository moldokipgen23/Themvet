@extends('layouts.admin')

@section('title', 'Edit Mock Test - ThemVet Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 style="font-size: 1.4rem; font-weight: 700;">Edit Mock Test</h2>
        <small class="text-muted">{{ $mockTest->title }}</small>
    </div>
    <a href="{{ route('admin.mock-tests.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger" style="border-radius: 8px;">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('admin.mock-tests.update', $mockTest->id) }}">
    @csrf
    @method('PUT')

    <div class="content-card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-medium">Exam *</label>
                    <select name="exam_id" class="form-select" id="mtExamSelect" required>
                        @foreach($exams as $exam)
                            <option value="{{ $exam->id }}" {{ $mockTest->exam_id == $exam->id ? 'selected' : '' }}
                                data-patterns="{{ json_encode($exam->patterns) }}">
                                {{ $exam->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Title *</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $mockTest->title) }}" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-medium">Description</label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description', $mockTest->description) }}</textarea>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium">Duration (min) *</label>
                    <input type="number" name="duration_minutes" class="form-control" value="{{ old('duration_minutes', $mockTest->duration_minutes) }}" min="1" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium">Total Marks *</label>
                    <input type="number" name="total_marks" class="form-control" value="{{ old('total_marks', $mockTest->total_marks) }}" min="1" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium">Total Questions</label>
                    <input type="number" name="total_questions" class="form-control" value="{{ old('total_questions', $mockTest->total_questions) }}" min="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium">Difficulty</label>
                    <select name="difficulty" class="form-select">
                        <option value="easy" {{ $mockTest->difficulty == 'easy' ? 'selected' : '' }}>Easy</option>
                        <option value="medium" {{ $mockTest->difficulty == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="hard" {{ $mockTest->difficulty == 'hard' ? 'selected' : '' }}>Hard</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="form-check form-switch mt-4">
                        <input class="form-check-input" type="checkbox" name="negative_marking" value="1" {{ $mockTest->negative_marking ? 'checked' : '' }} id="negMarkingCheck">
                        <label class="form-check-label" for="negMarkingCheck">Negative Marking</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium">Neg. Marks Value</label>
                    <input type="number" name="negative_marking_value" class="form-control" value="{{ old('negative_marking_value', $mockTest->negative_marking_value) }}" step="0.01" min="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium">Status</label>
                    <select name="status" class="form-select">
                        <option value="draft" {{ $mockTest->status == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ $mockTest->status == 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="content-card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="fw-semibold" style="font-size: 0.9rem;"><i class="fas fa-layer-group me-2"></i>Sections</span>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addSection()">
                <i class="fas fa-plus me-1"></i>Add Section
            </button>
        </div>
        <div class="card-body">
            <div id="sectionsContainer">
                @foreach($mockTest->sections as $section)
                    <div class="card mb-2" id="section-{{ $loop->index }}">
                        <div class="card-body p-3">
                            <div class="row g-2 align-items-end">
                                <input type="hidden" name="sections[{{ $loop->index }}][id]" value="{{ $section->id }}">
                                <div class="col-md-3">
                                    <label class="form-label" style="font-size: 0.8rem;">Section Name</label>
                                    <input type="text" name="sections[{{ $loop->index }}][name]" class="form-control form-control-sm" value="{{ $section->name }}" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label" style="font-size: 0.8rem;">Questions</label>
                                    <input type="number" name="sections[{{ $loop->index }}][total_questions]" class="form-control form-control-sm" value="{{ $section->total_questions }}" min="1" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label" style="font-size: 0.8rem;">Marks</label>
                                    <input type="number" name="sections[{{ $loop->index }}][total_marks]" class="form-control form-control-sm" value="{{ $section->total_marks }}" min="1" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label" style="font-size: 0.8rem;">Marks/Question</label>
                                    <input type="number" name="sections[{{ $loop->index }}][marks_per_question]" class="form-control form-control-sm" value="{{ $section->marks_per_question }}" step="0.01" min="0">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label" style="font-size: 0.8rem;">Neg. Marks/Q</label>
                                    <input type="number" name="sections[{{ $loop->index }}][negative_marks_per_question]" class="form-control form-control-sm" value="{{ $section->negative_marks_per_question }}" step="0.01" min="0">
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSection({{ $loop->index }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i> Save Changes
        </button>
        <button type="button" class="btn btn-outline-danger" onclick="if(confirm('Delete this mock test and all its questions?')){document.getElementById('delete-form').submit();}">
            <i class="fas fa-trash me-1"></i> Delete
        </button>
    </div>
</form>

<form method="POST" action="{{ route('admin.mock-tests.destroy', $mockTest->id) }}" id="delete-form" class="d-none">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
    let sectionIndex = {{ $mockTest->sections->count() }};

    function addSection(name, questions, marks, negMarks) {
        const container = document.getElementById('sectionsContainer');
        const html = `
            <div class="card mb-2" id="section-${sectionIndex}">
                <div class="card-body p-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label" style="font-size: 0.8rem;">Section Name</label>
                            <input type="text" name="sections[${sectionIndex}][name]" class="form-control form-control-sm" value="${name || ''}" required placeholder="e.g., Quantitative Aptitude">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" style="font-size: 0.8rem;">Questions</label>
                            <input type="number" name="sections[${sectionIndex}][total_questions]" class="form-control form-control-sm" value="${questions || 25}" min="1" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" style="font-size: 0.8rem;">Marks</label>
                            <input type="number" name="sections[${sectionIndex}][total_marks]" class="form-control form-control-sm" value="${marks || 25}" min="1" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" style="font-size: 0.8rem;">Marks/Question</label>
                            <input type="number" name="sections[${sectionIndex}][marks_per_question]" class="form-control form-control-sm" value="1" step="0.01" min="0">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" style="font-size: 0.8rem;">Neg. Marks/Q</label>
                            <input type="number" name="sections[${sectionIndex}][negative_marks_per_question]" class="form-control form-control-sm" value="${negMarks || 0.25}" step="0.01" min="0">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSection(${sectionIndex})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        sectionIndex++;
    }

    function removeSection(index) {
        const el = document.getElementById('section-' + index);
        if (el) el.remove();
    }

    // Auto-fill from exam pattern
    document.getElementById('mtExamSelect').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const patterns = JSON.parse(selected.getAttribute('data-patterns') || '[]');
        if (patterns.length > 0) {
            const p = patterns[0];
            document.querySelector('[name="duration_minutes"]').value = p.duration_minutes;
            document.querySelector('[name="total_marks"]').value = p.total_marks;
            document.querySelector('[name="total_questions"]').value = p.total_questions;
            document.querySelector('[name="negative_marking_value"]').value = p.negative_marking_value;
            document.querySelector('[name="negative_marking"]').checked = p.negative_marking;
            document.getElementById('sectionsContainer').innerHTML = '';
            sectionIndex = 0;
            if (p.sections) {
                p.sections.forEach(s => addSection(s.name, s.questions, s.marks, p.negative_marking_value));
            }
        }
    });
</script>
@endpush
