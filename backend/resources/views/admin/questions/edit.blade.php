@extends('layouts.admin')

@section('title', 'Edit Question - ThemVet Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 style="font-size: 1.4rem; font-weight: 700;">Edit Question</h2>
        <small class="text-muted">ID #{{ $question->id }}</small>
    </div>
    <a href="{{ route('admin.questions.index', ['exam_id' => $question->exam_id, 'subject_id' => $question->subject_id, 'topic_id' => $question->topic_id]) }}" class="btn btn-outline-secondary btn-sm">
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

<form method="POST" action="{{ route('admin.questions.update', $question->id) }}">
    @csrf
    @method('PUT')

    <div class="content-card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-medium">Exam *</label>
                    <select name="exam_id" class="form-select" id="editExamSelect" required>
                        @foreach($exams as $exam)
                            <option value="{{ $exam->id }}" {{ $question->exam_id == $exam->id ? 'selected' : '' }}>{{ $exam->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Subject *</label>
                    <select name="subject_id" class="form-select" id="editSubjectSelect" required>
                        @if($question->subject)
                            <option value="{{ $question->subject->id }}" selected>{{ $question->subject->name }}</option>
                        @endif
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Topic *</label>
                    <select name="topic_id" class="form-select" id="editTopicSelect" required>
                        @if($question->topic)
                            <option value="{{ $question->topic->id }}" selected>{{ $question->topic->name }}</option>
                        @endif
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Difficulty *</label>
                    <select name="difficulty" class="form-select" required>
                        <option value="easy" {{ $question->difficulty == 'easy' ? 'selected' : '' }}>Easy</option>
                        <option value="medium" {{ $question->difficulty == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="hard" {{ $question->difficulty == 'hard' ? 'selected' : '' }}>Hard</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">Question *</label>
                    <textarea name="question_text" class="form-control" rows="3" required>{{ old('question_text', $question->question_text) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">Explanation</label>
                    <textarea name="explanation" class="form-control" rows="2">{{ old('explanation', $question->explanation) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="content-card mb-4">
        <div class="card-header fw-semibold" style="font-size: 0.9rem;">
            <i class="fas fa-list-ul me-2"></i> Options
        </div>
        <div class="card-body">
            @foreach($question->options as $index => $option)
                <div class="d-flex align-items-start gap-3 mb-3 p-3 rounded border {{ $option->is_correct ? 'border-success bg-success bg-opacity-5' : '' }}">
                    <div class="form-check mt-1">
                        <input class="form-check-input" type="radio" name="correct_option" value="{{ $index }}" {{ $option->is_correct ? 'checked' : '' }}>
                    </div>
                    <div class="flex-grow-1">
                        <input type="text" name="options[{{ $index }}][option_text]" class="form-control" value="{{ old("options.$index.option_text", $option->option_text) }}" required placeholder="Option {{ $index + 1 }}">
                        <input type="hidden" name="options[{{ $index }}][is_correct]" value="0" class="is-correct-hidden">
                    </div>
                    @if($option->is_correct)
                        <span class="badge bg-success" style="font-size: 0.7rem;">Correct</span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i> Save Changes
        </button>
        <form method="POST" action="{{ route('admin.questions.destroy', $question->id) }}" onsubmit="return confirm('Delete this question?')" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">
                <i class="fas fa-trash me-1"></i> Delete
            </button>
        </form>
    </div>
</form>
@endsection

@push('scripts')
<script>
    // Correct option toggle
    document.querySelectorAll('input[name="correct_option"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.d-flex.align-items-start').forEach(row => {
                const r = row.querySelector('input[type="radio"]');
                const h = row.querySelector('.is-correct-hidden');
                const badge = row.querySelector('.badge');
                if (r.checked) {
                    h.value = '1';
                    row.classList.add('border-success', 'bg-success', 'bg-opacity-5');
                    if (!badge) {
                        const b = document.createElement('span');
                        b.className = 'badge bg-success';
                        b.style.cssText = 'font-size:0.7rem;';
                        b.textContent = 'Correct';
                        row.appendChild(b);
                    }
                } else {
                    h.value = '0';
                    row.classList.remove('border-success', 'bg-success', 'bg-opacity-5');
                    if (badge) badge.remove();
                }
            });
        });
    });

    // Cascading dropdowns
    const exams = @json($exams);
    const examSelect = document.getElementById('editExamSelect');
    const subjectSelect = document.getElementById('editSubjectSelect');
    const topicSelect = document.getElementById('editTopicSelect');

    function loadSubjects(examId, selectedId) {
        const exam = exams.find(e => e.id == examId);
        subjectSelect.innerHTML = '<option value="">Select Subject</option>';
        topicSelect.innerHTML = '<option value="">Select Topic</option>';
        if (exam && exam.subjects) {
            exam.subjects.forEach(s => {
                subjectSelect.innerHTML += `<option value="${s.id}" ${s.id == selectedId ? 'selected' : ''}>${s.name}</option>`;
            });
        }
    }

    function loadTopics(examId, subjectId, selectedId) {
        const exam = exams.find(e => e.id == examId);
        topicSelect.innerHTML = '<option value="">Select Topic</option>';
        if (exam && exam.subjects) {
            const subject = exam.subjects.find(s => s.id == subjectId);
            if (subject && subject.topics) {
                subject.topics.forEach(t => {
                    topicSelect.innerHTML += `<option value="${t.id}" ${t.id == selectedId ? 'selected' : ''}>${t.name}</option>`;
                });
            }
        }
    }

    examSelect.addEventListener('change', function() {
        loadSubjects(this.value, null);
    });

    subjectSelect.addEventListener('change', function() {
        loadTopics(examSelect.value, this.value, null);
    });

    // Init with current values
    loadSubjects(examSelect.value, {{ $question->subject_id }});
    loadTopics(examSelect.value, {{ $question->subject_id }}, {{ $question->topic_id }});
</script>
@endpush
