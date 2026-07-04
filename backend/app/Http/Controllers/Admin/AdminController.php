<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Attempt;
use App\Models\MockTest;
use App\Models\Notification;
use App\Models\Question;
use App\Models\Role;
use App\Models\ReviewerAssignment;
use App\Models\Setting;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            if (! $user->isAdmin()) {
                Auth::logout();
                return back()->withErrors(['email' => 'You do not have admin access.']);
            }

            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_questions' => Question::count(),
            'pending_questions' => Question::where('status', 'pending')->count(),
            'total_attempts' => \App\Models\Attempt::count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    public function users(Request $request)
    {
        $users = User::with(['roles', 'reviewerAssignments.exam', 'reviewerAssignments.subject'])
            ->latest()
            ->paginate(15);

        $exams = Exam::where('is_active', true)->get();

        return view('admin.users.index', compact('users', 'exams'));
    }

    public function assignRole(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        $role = Role::where('name', $request->role)->firstOrFail();
        $user->roles()->syncWithoutDetaching([$role->id]);

        return back()->with('success', "Role '{$role->name}' assigned to {$user->name}.");
    }

    public function exams()
    {
        $exams = Exam::with('subjects')->latest()->paginate(15);

        return view('admin.exams.index', compact('exams'));
    }

    public function storeExam(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Exam::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Exam created successfully.');
    }

    public function editExam($id)
    {
        $exam = Exam::with('subjects')->findOrFail($id);

        return view('admin.exams.edit', compact('exam'));
    }

    public function updateExam(Request $request, $id)
    {
        $exam = Exam::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $exam->update([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Exam updated successfully.');
    }

    public function questions(Request $request)
    {
        $query = Question::with(['exam', 'subject', 'topic']);

        if ($request->exam_id) {
            $query->where('exam_id', $request->exam_id);
        }
        if ($request->subject_id) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->topic_id) {
            $query->where('topic_id', $request->topic_id);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $questions = $query->latest()->paginate(20)->withQueryString();

        $exams = Exam::with(['subjects' => function ($q) {
            $q->with(['topics' => function ($t) {
                $t->withCount('questions');
            }])->withCount('questions');
        }])->withCount('questions')->orderBy('order')->get();

        $selectedExam = $request->exam_id ? Exam::with(['subjects' => function ($q) {
            $q->with(['topics' => function ($t) {
                $t->withCount('questions');
            }])->withCount('questions');
        }])->find($request->exam_id) : null;

        $selectedSubject = $request->subject_id ? Subject::with(['topics' => function ($t) {
            $t->withCount('questions');
        }])->withCount('questions')->find($request->subject_id) : null;

        $selectedTopic = $request->topic_id ? Topic::withCount('questions')->find($request->topic_id) : null;

        return view('admin.questions.index', compact(
            'questions', 'exams', 'selectedExam', 'selectedSubject', 'selectedTopic'
        ));
    }

    public function mockTests()
    {
        $mockTests = MockTest::with(['exam', 'sections', 'questions'])
            ->latest()
            ->paginate(20);

        $exams = Exam::orderBy('order')->get();

        return view('admin.mock-tests.index', compact('mockTests', 'exams'));
    }

    public function storeMockTest(Request $request)
    {
        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1',
            'total_marks' => 'required|integer|min:1',
            'total_questions' => 'sometimes|integer|min:0',
            'difficulty' => 'sometimes|in:easy,medium,hard',
            'negative_marking' => 'sometimes|boolean',
            'negative_marking_value' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:draft,published',
            'sections' => 'sometimes|array',
            'sections.*.name' => 'required|string',
            'sections.*.total_questions' => 'required|integer|min:1',
            'sections.*.total_marks' => 'required|integer|min:1',
            'sections.*.marks_per_question' => 'sometimes|numeric|min:0',
            'sections.*.negative_marks_per_question' => 'sometimes|numeric|min:0',
        ]);

        $mockTest = MockTest::create([
            'exam_id' => $validated['exam_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'duration_minutes' => $validated['duration_minutes'],
            'total_marks' => $validated['total_marks'],
            'total_questions' => $validated['total_questions'] ?? 0,
            'difficulty' => $validated['difficulty'] ?? 'medium',
            'negative_marking' => $validated['negative_marking'] ?? false,
            'negative_marking_value' => $validated['negative_marking_value'] ?? 0,
            'status' => $validated['status'] ?? 'draft',
            'created_by' => Auth::id(),
        ]);

        if (!empty($validated['sections'])) {
            foreach ($validated['sections'] as $index => $section) {
                \App\Models\MockTestSection::create([
                    'mock_test_id' => $mockTest->id,
                    'name' => $section['name'],
                    'total_questions' => $section['total_questions'],
                    'total_marks' => $section['total_marks'],
                    'marks_per_question' => $section['marks_per_question'] ?? 1,
                    'negative_marks_per_question' => $section['negative_marks_per_question'] ?? 0,
                    'is_mandatory' => true,
                    'order' => $index + 1,
                ]);
            }
            $mockTest->update(['total_questions' => $mockTest->sections()->sum('total_questions')]);
        }

        return back()->with('success', 'Mock test created successfully.');
    }

    public function showQuestion($id)
    {
        $question = Question::with(['exam', 'subject', 'topic', 'options', 'contributor'])->findOrFail($id);
        return response()->json($question);
    }

    public function editQuestion($id)
    {
        $question = Question::with(['exam', 'subject', 'topic', 'options'])->findOrFail($id);
        $exams = Exam::orderBy('order')->get();
        return view('admin.questions.edit', compact('question', 'exams'));
    }

    public function updateQuestion(Request $request, $id)
    {
        $question = Question::findOrFail($id);

        $validated = $request->validate([
            'question_text' => 'required|string',
            'exam_id' => 'required|exists:exams,id',
            'subject_id' => 'required|exists:subjects,id',
            'topic_id' => 'required|exists:topics,id',
            'difficulty' => 'required|in:easy,medium,hard',
            'explanation' => 'nullable|string',
            'options' => 'required|array|min:2',
            'options.*.option_text' => 'required|string',
            'options.*.is_correct' => 'required|boolean',
        ]);

        $question->update([
            'question_text' => $validated['question_text'],
            'exam_id' => $validated['exam_id'],
            'subject_id' => $validated['subject_id'],
            'topic_id' => $validated['topic_id'],
            'difficulty' => $validated['difficulty'],
            'explanation' => $validated['explanation'] ?? null,
        ]);

        foreach ($validated['options'] as $index => $option) {
            if (isset($question->options[$index])) {
                $question->options[$index]->update([
                    'option_text' => $option['option_text'],
                    'is_correct' => $option['is_correct'],
                ]);
            }
        }

        return redirect()->route('admin.questions.index')->with('success', 'Question updated.');
    }

    public function destroyQuestion($id)
    {
        $question = Question::findOrFail($id);
        $question->options()->delete();
        $question->delete();
        return back()->with('success', 'Question deleted.');
    }

    public function editMockTest($id)
    {
        $mockTest = MockTest::with(['exam', 'sections', 'questions.question.options'])->findOrFail($id);
        $exams = Exam::with('patterns')->orderBy('order')->get();
        return view('admin.mock-tests.edit', compact('mockTest', 'exams'));
    }

    public function updateMockTest(Request $request, $id)
    {
        $mockTest = MockTest::findOrFail($id);

        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1',
            'total_marks' => 'required|integer|min:1',
            'total_questions' => 'sometimes|integer|min:0',
            'difficulty' => 'sometimes|in:easy,medium,hard',
            'negative_marking' => 'sometimes|boolean',
            'negative_marking_value' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:draft,published',
            'sections' => 'sometimes|array',
            'sections.*.id' => 'sometimes|integer',
            'sections.*.name' => 'required|string',
            'sections.*.total_questions' => 'required|integer|min:1',
            'sections.*.total_marks' => 'required|integer|min:1',
            'sections.*.marks_per_question' => 'sometimes|numeric|min:0',
            'sections.*.negative_marks_per_question' => 'sometimes|numeric|min:0',
        ]);

        $mockTest->update([
            'exam_id' => $validated['exam_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'duration_minutes' => $validated['duration_minutes'],
            'total_marks' => $validated['total_marks'],
            'total_questions' => $validated['total_questions'] ?? $mockTest->total_questions,
            'difficulty' => $validated['difficulty'] ?? $mockTest->difficulty,
            'negative_marking' => $validated['negative_marking'] ?? $mockTest->negative_marking,
            'negative_marking_value' => $validated['negative_marking_value'] ?? $mockTest->negative_marking_value,
            'status' => $validated['status'] ?? $mockTest->status,
        ]);

        if (isset($validated['sections'])) {
            $existingIds = collect($validated['sections'])->pluck('id')->filter()->toArray();

            \App\Models\MockTestQuestion::whereIn('mock_test_section_id', 
                $mockTest->sections()->whereNotIn('id', $existingIds)->pluck('id')
            )->update(['mock_test_section_id' => null]);

            $mockTest->sections()->whereNotIn('id', $existingIds)->delete();

            foreach ($validated['sections'] as $index => $section) {
                if (!empty($section['id'])) {
                    \App\Models\MockTestSection::where('id', $section['id'])->update([
                        'name' => $section['name'],
                        'total_questions' => $section['total_questions'],
                        'total_marks' => $section['total_marks'],
                        'marks_per_question' => $section['marks_per_question'] ?? 1,
                        'negative_marks_per_question' => $section['negative_marks_per_question'] ?? 0,
                        'order' => $index + 1,
                    ]);
                } else {
                    \App\Models\MockTestSection::create([
                        'mock_test_id' => $mockTest->id,
                        'name' => $section['name'],
                        'total_questions' => $section['total_questions'],
                        'total_marks' => $section['total_marks'],
                        'marks_per_question' => $section['marks_per_question'] ?? 1,
                        'negative_marks_per_question' => $section['negative_marks_per_question'] ?? 0,
                        'is_mandatory' => true,
                        'order' => $index + 1,
                    ]);
                }
            }
        }

        return redirect()->route('admin.mock-tests.index')->with('success', 'Mock test updated.');
    }

    public function destroyMockTest($id)
    {
        $mockTest = MockTest::findOrFail($id);

        $sectionIds = $mockTest->sections()->pluck('id')->toArray();
        \App\Models\MockTestQuestion::where('mock_test_id', $id)->update(['mock_test_section_id' => null]);

        $mockTest->sections()->delete();
        $mockTest->questions()->delete();
        $mockTest->delete();

        return back()->with('success', 'Mock test deleted.');
    }

    public function updateMockTestStatus(Request $request, $id)
    {
        $mockTest = MockTest::findOrFail($id);

        $request->validate([
            'status' => 'required|string|in:draft,published,closed',
        ]);

        $mockTest->update(['status' => $request->status]);

        return back()->with('success', 'Mock test status updated.');
    }

    public function reviewerAssignments()
    {
        $assignments = ReviewerAssignment::with(['user', 'exam', 'subject'])
            ->latest()
            ->paginate(20);

        return view('admin.reviewers.assignments', compact('assignments'));
    }

    public function storeReviewerAssignment(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'exam_id' => 'required|exists:exams,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'level' => 'required|string|in:reviewer,lead_reviewer',
        ]);

        $exists = ReviewerAssignment::where('user_id', $request->user_id)
            ->where('exam_id', $request->exam_id)
            ->where('subject_id', $request->subject_id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'This assignment already exists.');
        }

        ReviewerAssignment::create([
            'user_id' => $request->user_id,
            'exam_id' => $request->exam_id,
            'subject_id' => $request->subject_id,
            'level' => $request->level,
            'is_active' => true,
        ]);

        return back()->with('success', 'Reviewer assigned successfully.');
    }

    public function destroyReviewerAssignment($id)
    {
        $assignment = ReviewerAssignment::findOrFail($id);
        $assignment->delete();

        return back()->with('success', 'Assignment removed.');
    }

    public function reviewQueue()
    {
        $questions = Question::with(['exam', 'subject', 'contributor'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(20);

        return view('admin.review-queue', compact('questions'));
    }

    public function approveQuestion($id)
    {
        $question = Question::findOrFail($id);
        $question->update(['status' => 'approved']);

        return back()->with('success', "Question #{$id} has been approved.");
    }

    public function rejectQuestion(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $question = Question::findOrFail($id);
        $question->update([
            'status' => 'rejected',
        ]);

        return back()->with('success', "Question #{$id} has been rejected.");
    }

    public function settings()
    {
        $settings = Setting::all()->pluck('value', 'key');
        return view('admin.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'nullable|string|max:255',
            'site_tagline' => 'nullable|string|max:255',
            'site_description' => 'nullable|string|max:500',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_address' => 'nullable|string|max:500',
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'smtp_encryption' => 'nullable|string|in:tls,ssl',
            'smtp_from_email' => 'nullable|email|max:255',
            'smtp_from_name' => 'nullable|string|max:255',
            'default_duration_minutes' => 'nullable|integer|min:5|max:180',
            'default_negative_marking' => 'nullable|boolean',
            'default_negative_marking_value' => 'nullable|numeric|min:0|max:10',
            'passing_percentage' => 'nullable|numeric|min:0|max:100',
            'registration_enabled' => 'nullable|boolean',
            'maintenance_mode' => 'nullable|boolean',
            'firebase_project_id' => 'nullable|string|max:255',
            'firebase_key' => 'nullable|string',
        ]);

        $booleanFields = ['default_negative_marking', 'registration_enabled', 'maintenance_mode'];

        foreach ($validated as $key => $value) {
            if (in_array($key, $booleanFields)) {
                Setting::set($key, $request->boolean($key));
            } else {
                Setting::set($key, $value);
            }
        }

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('settings', 'public');
            Setting::set('logo', $path);
        }
        if ($request->hasFile('favicon')) {
            $path = $request->file('favicon')->store('settings', 'public');
            Setting::set('favicon', $path);
        }

        return back()->with('success', 'Settings updated successfully.');
    }

    public function getSettings()
    {
        $settings = Setting::all()->pluck('value', 'key');
        return response()->json([
            'status' => 'success',
            'data' => $settings,
        ]);
    }

    public function analytics()
    {
        $totalUsers = User::count();
        $totalQuestions = Question::count();
        $pendingQuestions = Question::where('status', 'pending')->count();
        $approvedQuestions = Question::where('status', 'approved')->count();
        $totalAttempts = Attempt::where('status', 'completed')->count();
        $totalMockTests = MockTest::count();
        $publishedTests = MockTest::where('status', 'published')->count();
        $todayAttempts = Attempt::where('status', 'completed')
            ->whereDate('created_at', today())
            ->count();
        $weekAttempts = Attempt::where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $attemptsByDay = Attempt::where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(14))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $usersByRole = Role::withCount('users')->get();

        $topExams = Exam::withCount(['mockTests', 'questions'])
            ->orderByDesc('mock_tests_count')
            ->limit(5)
            ->get();

        $questionsByStatus = [
            'total' => $totalQuestions,
            'pending' => $pendingQuestions,
            'approved' => $approvedQuestions,
            'rejected' => Question::where('status', 'rejected')->count(),
            'draft' => Question::where('status', 'draft')->count(),
        ];

        return view('admin.analytics', compact(
            'totalUsers', 'totalQuestions', 'pendingQuestions', 'approvedQuestions',
            'totalAttempts', 'totalMockTests', 'publishedTests',
            'todayAttempts', 'weekAttempts',
            'attemptsByDay', 'usersByRole', 'topExams', 'questionsByStatus'
        ));
    }

    public function examSubjects($examId)
    {
        $subjects = Subject::where('exam_id', $examId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($subjects);
    }

    // Subjects CRUD
    public function subjects()
    {
        $subjects = Subject::with('exam', 'topics')->latest()->paginate(20);
        $exams = Exam::where('is_active', true)->get();

        return view('admin.subjects.index', compact('subjects', 'exams'));
    }

    public function storeSubject(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'exam_id' => 'required|exists:exams,id',
            'description' => 'nullable|string',
        ]);

        Subject::create([
            'name' => $request->name,
            'slug' => \Illuminate\Support\Str::slug($request->name),
            'exam_id' => $request->exam_id,
            'description' => $request->description,
            'is_active' => true,
        ]);

        return back()->with('success', 'Subject created successfully.');
    }

    public function updateSubject(Request $request, $id)
    {
        $subject = Subject::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'exam_id' => 'required|exists:exams,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $subject->update([
            'name' => $request->name,
            'slug' => \Illuminate\Support\Str::slug($request->name),
            'exam_id' => $request->exam_id,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Subject updated successfully.');
    }

    public function destroySubject($id)
    {
        $subject = Subject::findOrFail($id);
        $subject->delete();

        return back()->with('success', 'Subject deleted successfully.');
    }

    // Topics CRUD
    public function topics()
    {
        $topics = Topic::with('subject.exam', 'questions')->latest()->paginate(20);
        $subjects = Subject::with('exam')->where('is_active', true)->orderBy('name')->get();

        return view('admin.topics.index', compact('topics', 'subjects'));
    }

    public function storeTopic(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'description' => 'nullable|string',
        ]);

        Topic::create([
            'name' => $request->name,
            'slug' => \Illuminate\Support\Str::slug($request->name),
            'subject_id' => $request->subject_id,
            'description' => $request->description,
            'is_active' => true,
        ]);

        return back()->with('success', 'Topic created successfully.');
    }

    public function updateTopic(Request $request, $id)
    {
        $topic = Topic::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $topic->update([
            'name' => $request->name,
            'slug' => \Illuminate\Support\Str::slug($request->name),
            'subject_id' => $request->subject_id,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Topic updated successfully.');
    }

    public function destroyTopic($id)
    {
        $topic = Topic::findOrFail($id);
        $topic->delete();

        return back()->with('success', 'Topic deleted successfully.');
    }

    // Roles
    public function roles()
    {
        $roles = Role::withCount('users')->get();

        return view('admin.roles.index', compact('roles'));
    }

    // Notifications
    public function notifications()
    {
        $notifications = Notification::with('user')->latest()->paginate(20);
        $users = User::where('is_active', true)->orderBy('name')->get();

        return view('admin.notifications.index', compact('notifications', 'users'));
    }

    public function storeNotification(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'sometimes|string|in:general,achievement,announcement,reminder',
        ]);

        if ($request->user_id === 'all') {
            $users = User::where('is_active', true)->pluck('id');
            foreach ($users as $userId) {
                Notification::create([
                    'user_id' => $userId,
                    'title' => $request->title,
                    'message' => $request->message,
                    'type' => $request->type ?? 'general',
                ]);
            }
        } else {
            Notification::create([
                'user_id' => $request->user_id,
                'title' => $request->title,
                'message' => $request->message,
                'type' => $request->type ?? 'general',
            ]);
        }

        return back()->with('success', 'Notification sent successfully.');
    }
}
