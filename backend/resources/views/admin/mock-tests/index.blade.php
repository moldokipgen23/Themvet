@extends('layouts.admin')

@section('title', 'Mock Tests - ThemVet Admin')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>Mock Tests</h2>
            <p>Manage mock tests organized by exam with section-level structure</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createMockTestModal">
            <i class="fas fa-plus me-2"></i>Create Mock Test
        </button>
    </div>
</div>

<!-- Exam Tabs -->
<ul class="nav nav-pills mb-4">
    <li class="nav-item">
        <a class="nav-link {{ !request('exam_id') ? 'active' : '' }}" href="{{ route('admin.mock-tests.index') }}">All</a>
    </li>
    @foreach($exams as $exam)
        <li class="nav-item">
            <a class="nav-link {{ request('exam_id') == $exam->id ? 'active' : '' }}" href="{{ route('admin.mock-tests.index', ['exam_id' => $exam->id]) }}">
                {{ $exam->name }}
                <span class="badge bg-light text-dark ms-1">{{ $exam->mockTests->count() }}</span>
            </a>
        </li>
    @endforeach
</ul>

<!-- Mock Tests List -->
<div class="content-card">
    <div class="card-body p-0">
        @if($mockTests->count() == 0)
            <div class="text-center py-5">
                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                <p class="text-muted">No mock tests found</p>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createMockTestModal">
                    <i class="fas fa-plus me-1"></i> Create First Mock Test
                </button>
            </div>
        @else
            @foreach($mockTests as $mockTest)
                <div class="border-bottom p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <h5 class="mb-0 fw-bold">{{ $mockTest->title }}</h5>
                                @if($mockTest->is_official)
                                    <span class="badge bg-primary">OFFICIAL</span>
                                @endif
                                <span class="badge bg-{{ $mockTest->status == 'published' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($mockTest->status) }}
                                </span>
                                @if($mockTest->difficulty)
                                    <span class="badge bg-{{ $mockTest->difficulty == 'easy' ? 'success' : ($mockTest->difficulty == 'hard' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($mockTest->difficulty) }}
                                    </span>
                                @endif
                            </div>
                            <div class="text-muted mb-2" style="font-size: 0.85rem;">
                                <i class="fas fa-book me-1"></i> {{ $mockTest->exam->name ?? 'Unknown Exam' }}
                                @if($mockTest->description)
                                    <span class="ms-2">|</span> {{ Str::limit($mockTest->description, 80) }}
                                @endif
                            </div>
                            <div class="d-flex gap-3 mb-2" style="font-size: 0.85rem;">
                                <span><i class="fas fa-clock me-1 text-muted"></i> {{ $mockTest->duration_minutes }} min</span>
                                <span><i class="fas fa-star me-1 text-muted"></i> {{ $mockTest->total_marks }} marks</span>
                                <span><i class="fas fa-question-circle me-1 text-muted"></i> {{ $mockTest->total_questions }} questions</span>
                                @if($mockTest->negative_marking)
                                    <span class="text-danger"><i class="fas fa-minus-circle me-1"></i> -{{ $mockTest->negative_marking_value }}</span>
                                @endif
                            </div>

                            <!-- Sections -->
                            @if($mockTest->sections->count() > 0)
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    @foreach($mockTest->sections as $section)
                                        <div class="px-3 py-2 rounded" style="background: #f0f4ff; border: 1px solid #dbeafe;">
                                            <div class="fw-medium" style="font-size: 0.8rem; color: #3b5998;">
                                                {{ $section->name }}
                                            </div>
                                            <div style="font-size: 0.75rem; color: #6b7280;">
                                                {{ $section->total_questions }}q / {{ $section->total_marks }}m
                                                @if($section->duration_minutes)
                                                    | {{ $section->duration_minutes }}min
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <a href="{{ route('admin.mock-tests.edit', $mockTest->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit me-1"></i>Edit
                            </a>
                            <form action="{{ route('admin.mock-tests.status', $mockTest->id) }}" method="POST">
                                @csrf @method('PUT')
                                <input type="hidden" name="status" value="{{ $mockTest->status == 'published' ? 'draft' : 'published' }}">
                                <button type="submit" class="btn btn-sm btn-{{ $mockTest->status == 'published' ? 'outline-warning' : 'outline-success' }}">
                                    <i class="fas fa-{{ $mockTest->status == 'published' ? 'eye-slash' : 'check' }} me-1"></i>
                                    {{ $mockTest->status == 'published' ? 'Unpublish' : 'Publish' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
            <div class="p-3">
                {{ $mockTests->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Create Mock Test Modal -->
<div class="modal fade" id="createMockTestModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form action="{{ route('admin.mock-tests.store') }}" method="POST" id="createMockTestForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Create Mock Test</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Exam *</label>
                            <select name="exam_id" class="form-select" id="mtExamSelect" required>
                                <option value="">Select Exam</option>
                                @foreach($exams as $exam)
                                    <option value="{{ $exam->id }}" data-patterns="{{ json_encode($exam->patterns) }}">
                                        {{ $exam->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Title *</label>
                            <input type="text" name="title" class="form-control" required placeholder="e.g., SSC CGL Tier 1 Mock Test - 2">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-medium">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Optional description"></textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">Duration (min) *</label>
                            <input type="number" name="duration_minutes" class="form-control" value="60" min="1" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">Total Marks *</label>
                            <input type="number" name="total_marks" class="form-control" value="100" min="1" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">Total Questions</label>
                            <input type="number" name="total_questions" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">Difficulty</label>
                            <select name="difficulty" class="form-select">
                                <option value="easy">Easy</option>
                                <option value="medium" selected>Medium</option>
                                <option value="hard">Hard</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="negative_marking" value="1" checked id="negMarkingCheck">
                                <label class="form-check-label" for="negMarkingCheck">Negative Marking</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">Neg. Marks Value</label>
                            <input type="number" name="negative_marking_value" class="form-control" value="0.25" step="0.01" min="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">Status</label>
                            <select name="status" class="form-select">
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                            </select>
                        </div>

                        <!-- Sections -->
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mt-3 mb-2">
                                <label class="form-label fw-bold mb-0"><i class="fas fa-layer-group me-2"></i>Sections</label>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addSection()">
                                    <i class="fas fa-plus me-1"></i>Add Section
                                </button>
                            </div>
                            <div id="sectionsContainer">
                                <!-- Sections added dynamically -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Create Mock Test
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let sectionIndex = 0;

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
            const p = patterns[0]; // Use first pattern
            document.querySelector('[name="duration_minutes"]').value = p.duration_minutes;
            document.querySelector('[name="total_marks"]').value = p.total_marks;
            document.querySelector('[name="total_questions"]').value = p.total_questions;
            document.querySelector('[name="negative_marking_value"]').value = p.negative_marking_value;
            document.querySelector('[name="negative_marking"]').checked = p.negative_marking;

            // Clear existing sections and add from pattern
            document.getElementById('sectionsContainer').innerHTML = '';
            sectionIndex = 0;
            if (p.sections) {
                p.sections.forEach(s => addSection(s.name, s.questions, s.marks, p.negative_marking_value));
            }
        }
    });
</script>
@endpush
