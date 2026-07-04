@extends('layouts.admin')

@section('title', 'Questions - ThemVet Admin')

@section('content')
<div class="page-header">
    <h2>Questions</h2>
</div>

{{-- LEVEL 1: Exam cards --}}
@if(!$selectedExam)
    <div class="row g-3">
        @foreach($exams as $exam)
            <div class="col-md-4">
                <a href="{{ route('admin.questions.index', ['exam_id' => $exam->id]) }}" class="text-decoration-none">
                    <div class="content-card h-100 hover-lift">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                                 style="width: 50px; height: 50px; background: #eef2ff;">
                                <i class="fas fa-book" style="color: #6366f1;"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-1">{{ $exam->name }}</h6>
                            <span class="badge bg-primary">{{ $exam->questions_count }} questions</span>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
@endif

{{-- LEVEL 2: Subject cards --}}
@if($selectedExam && !$selectedSubject)
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.questions.index') }}" class="btn btn-sm btn-outline-secondary me-3">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h5 class="fw-bold mb-0">{{ $selectedExam->name }}</h5>
    </div>
    <div class="row g-3">
        @foreach($selectedExam->subjects as $subject)
            <div class="col-md-4">
                <a href="{{ route('admin.questions.index', ['exam_id' => $selectedExam->id, 'subject_id' => $subject->id]) }}" class="text-decoration-none">
                    <div class="content-card h-100 hover-lift">
                        <div class="card-body py-4">
                            <div class="d-flex align-items-center mb-2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                                     style="width: 40px; height: 40px; background: #ecfdf5;">
                                    <i class="fas fa-folder" style="color: #10b981; font-size: 0.9rem;"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-0" style="font-size: 0.95rem;">{{ $subject->name }}</h6>
                                    <small class="text-muted">{{ $subject->topics->count() }} topics</small>
                                </div>
                            </div>
                            <span class="badge bg-success">{{ $subject->questions_count }} questions</span>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
@endif

{{-- LEVEL 3: Topic cards --}}
@if($selectedSubject && !$selectedTopic)
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.questions.index', ['exam_id' => $selectedExam->id]) }}" class="btn btn-sm btn-outline-secondary me-3">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h5 class="fw-bold mb-0">{{ $selectedSubject->name }}</h5>
    </div>
    <div class="row g-3">
        @foreach($selectedSubject->topics as $topic)
            <div class="col-md-4">
                <a href="{{ route('admin.questions.index', ['exam_id' => $selectedExam->id, 'subject_id' => $selectedSubject->id, 'topic_id' => $topic->id]) }}" class="text-decoration-none">
                    <div class="content-card h-100 hover-lift">
                        <div class="card-body py-4">
                            <div class="d-flex align-items-center mb-2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                                     style="width: 40px; height: 40px; background: #fef3c7;">
                                    <i class="fas fa-tag" style="color: #f59e0b; font-size: 0.9rem;"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-0" style="font-size: 0.95rem;">{{ $topic->name }}</h6>
                            </div>
                            <span class="badge bg-warning text-dark">{{ $topic->questions_count }} questions</span>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
@endif

{{-- LEVEL 4: Questions table --}}
@if($selectedTopic)
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.questions.index', ['exam_id' => $selectedExam->id, 'subject_id' => $selectedSubject->id]) }}" class="btn btn-sm btn-outline-secondary me-3">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h5 class="fw-bold mb-0">{{ $selectedTopic->name }}</h5>
            <small class="text-muted">{{ $selectedExam->name }} / {{ $selectedSubject->name }}</small>
        </div>
        <span class="badge bg-primary ms-auto">{{ $questions->total() }} questions</span>
    </div>
    <div class="content-card">
        <div class="card-body p-0">
            @if($questions->count() == 0)
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-2x text-muted mb-3"></i>
                    <p class="text-muted mb-0">No questions in this topic</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width: 40px;">#</th>
                                <th>Question</th>
                                <th style="width: 90px;">Difficulty</th>
                                <th style="width: 80px;">Status</th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($questions as $question)
                                <tr>
                                    <td class="text-muted" style="font-size: 0.8rem;">{{ $question->id }}</td>
                                    <td style="font-size: 0.875rem;">{{ Str::limit($question->question_text, 90) }}</td>
                                    <td>
                                        @if($question->difficulty == 'easy')
                                            <span class="badge bg-success">Easy</span>
                                        @elseif($question->difficulty == 'hard')
                                            <span class="badge bg-danger">Hard</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Medium</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($question->status == 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($question->status == 'pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @elseif($question->status == 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-secondary">Draft</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-secondary py-0" onclick="viewQuestion({{ $question->id }})">
                                            <i class="fas fa-eye" style="font-size: 0.75rem;"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3">{{ $questions->withQueryString()->links() }}</div>
            @endif
        </div>
    </div>
@endif

<!-- Question Modal -->
<div class="modal fade" id="questionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 12px;">
            <div class="modal-header" style="border-bottom: 1px solid #f1f5f9;">
                <h6 class="modal-title fw-bold">Question</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="questionModalBody">
                <div class="text-center py-4"><div class="spinner-border spinner-border-sm"></div></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<style>
    .hover-lift { transition: transform 0.12s, box-shadow 0.12s; cursor: pointer; }
    .hover-lift:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
</style>
<script>
    function viewQuestion(id) {
        const modal = new bootstrap.Modal(document.getElementById('questionModal'));
        document.getElementById('questionModalBody').innerHTML = '<div class="text-center py-4"><div class="spinner-border spinner-border-sm"></div></div>';
        modal.show();
        fetch('/admin/questions/' + id, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(q => {
            let opts = '';
            if (q.options) {
                const L = ['A','B','C','D','E','F'];
                q.options.forEach((o, i) => {
                    const cls = o.is_correct ? 'border-success bg-success bg-opacity-10' : '';
                    opts += `<div class="p-2 mb-2 rounded border ${cls}"><strong>${L[i]}.</strong> ${o.option_text}${o.is_correct ? ' <span class="badge bg-success ms-2" style="font-size:0.65rem;">Correct</span>' : ''}</div>`;
                });
            }
            document.getElementById('questionModalBody').innerHTML = `
                <div class="mb-2"><span class="badge bg-light text-dark" style="font-size:0.75rem;">${q.exam?.name||'-'} / ${q.subject?.name||'-'} / ${q.topic?.name||'-'}</span>
                <span class="badge bg-${q.difficulty==='easy'?'success':q.difficulty==='hard'?'danger':'warning'}" style="font-size:0.75rem;">${q.difficulty}</span></div>
                <p class="fw-semibold">${q.question_text}</p><hr>${opts}
                ${q.explanation ? `<div class="mt-3 p-3 bg-light rounded" style="font-size:0.875rem;"><strong>Explanation:</strong> ${q.explanation}</div>` : ''}
                <div class="mt-3 d-flex gap-2">
                    <a href="/admin/questions/${q.id}/edit" class="btn btn-sm btn-primary"><i class="fas fa-edit me-1"></i>Edit</a>
                    <form method="POST" action="/admin/questions/${q.id}" onsubmit="return confirm('Delete this question?')">
                        <input type="hidden" name="_token" value="${document.querySelector('meta[name=csrf-token]').content}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash me-1"></i>Delete</button>
                    </form>
                </div>`;
        })
        .catch(() => { document.getElementById('questionModalBody').innerHTML = '<div class="text-danger">Failed to load</div>'; });
    }
</script>
@endpush
