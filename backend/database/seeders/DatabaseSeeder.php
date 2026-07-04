<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Exam;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\MockTest;
use App\Models\MockTestQuestion;
use App\Models\Badge;
use App\Models\Setting;
use App\Models\ExamPattern;
use App\Models\ExamSection;
use App\Models\MockTestSection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedRoles();
        $this->seedExamsAndContent();
        $this->seedExamPatterns();
        $this->seedUsers();
        $this->seedBadges();
        $this->seedSettings();
        $this->seedMockTests();
    }

    private function seedRoles(): void
    {
        $roles = [
            ['name' => 'admin', 'group' => 'system', 'description' => 'Full platform management and settings'],
            ['name' => 'moderator', 'group' => 'system', 'description' => 'Content moderation, user reports, light admin'],
            ['name' => 'teacher', 'group' => 'teacher', 'description' => 'Create questions, submit test drafts, view contributions'],
            ['name' => 'reviewer', 'group' => 'teacher', 'description' => 'Review, approve or reject submitted questions'],
            ['name' => 'lead_reviewer', 'group' => 'teacher', 'description' => 'Senior reviewer, create official tests, override decisions'],
            ['name' => 'student', 'group' => 'student', 'description' => 'Take mock tests, practice questions, compete on leaderboards'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }

    private function seedUsers(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@themvet.com',
            'password' => Hash::make('password'),
        ]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());

        $student = User::create([
            'name' => 'Test Student',
            'email' => 'student@themvet.com',
            'password' => Hash::make('password'),
            'target_exam_id' => Exam::where('slug', 'ssc')->first()->id,
        ]);
        $student->roles()->attach(Role::where('name', 'student')->first());

        $teacher = User::create([
            'name' => 'Test Teacher',
            'email' => 'contributor@themvet.com',
            'password' => Hash::make('password'),
        ]);
        $teacher->roles()->attach(Role::where('name', 'teacher')->first());

        $reviewer = User::create([
            'name' => 'Test Reviewer',
            'email' => 'reviewer@themvet.com',
            'password' => Hash::make('password'),
        ]);
        $reviewer->roles()->attach(Role::where('name', 'reviewer')->first());
    }

    private function seedBadges(): void
    {
        $badges = [
            ['name' => 'first_test', 'description' => 'Complete your first test', 'icon' => 'star', 'color' => '#FFD700', 'category' => 'general', 'points' => 10],
            ['name' => 'five_tests', 'description' => 'Complete 5 tests', 'icon' => 'stars', 'color' => '#FFA500', 'category' => 'general', 'points' => 50],
            ['name' => 'ten_tests', 'description' => 'Complete 10 tests', 'icon' => 'emoji_events', 'color' => '#FF6347', 'category' => 'general', 'points' => 100],
            ['name' => 'hundred_questions', 'description' => 'Attempt 100 questions', 'icon' => 'psychology', 'color' => '#9C27B0', 'category' => 'practice', 'points' => 75],
            ['name' => 'perfect_score', 'description' => 'Get 100% accuracy in a test', 'icon' => 'shield', 'color' => '#4CAF50', 'category' => 'achievement', 'points' => 200],
            ['name' => 'high_achiever', 'description' => 'Average 80%+ accuracy across 5+ tests', 'icon' => 'workspace_premium', 'color' => '#2196F3', 'category' => 'achievement', 'points' => 150],
            ['name' => 'three_day_streak', 'description' => 'Study 3 days in a row', 'icon' => 'local_fire_department', 'color' => '#FF9800', 'category' => 'streak', 'points' => 30],
            ['name' => 'seven_day_streak', 'description' => 'Study 7 days in a row', 'icon' => 'local_fire_department', 'color' => '#FF5722', 'category' => 'streak', 'points' => 70],
            ['name' => 'thirty_day_streak', 'description' => 'Study 30 days in a row', 'icon' => 'whatshot', 'color' => '#D32F2F', 'category' => 'streak', 'points' => 300],
            ['name' => 'points_100', 'description' => 'Earn 100 points', 'icon' => 'toll', 'color' => '#FFD700', 'category' => 'points', 'points' => 0],
            ['name' => 'points_500', 'description' => 'Earn 500 points', 'icon' => 'toll', 'color' => '#C0C0C0', 'category' => 'points', 'points' => 0],
            ['name' => 'points_1000', 'description' => 'Earn 1000 points', 'icon' => 'toll', 'color' => '#FFD700', 'category' => 'points', 'points' => 0],
        ];

        foreach ($badges as $badge) {
            Badge::firstOrCreate(['name' => $badge['name']], $badge);
        }
    }

    private function seedSettings(): void
    {
        $defaults = [
            'default_duration_minutes' => 60,
            'default_negative_marking' => true,
            'default_negative_marking_value' => 0.25,
            'passing_percentage' => 40,
        ];
        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }

    private function seedExamPatterns(): void
    {
        // ==================== SSC CGL ====================
        $ssc = Exam::where('slug', 'ssc')->first();
        $sscTier1 = ExamPattern::create([
            'exam_id' => $ssc->id,
            'name' => 'SSC CGL Tier 1',
            'slug' => 'ssc-cgl-tier1',
            'description' => 'SSC CGL Tier 1 - Computer Based Examination. 100 questions, 60 minutes, 200 marks.',
            'duration_minutes' => 60,
            'total_marks' => 200,
            'total_questions' => 100,
            'sections_count' => 4,
            'negative_marking' => true,
            'negative_marking_value' => 0.50,
            'is_official' => true,
            'order' => 1,
            'details' => [
                'qualification' => 'All candidates qualifying Tier 1 appear for Tier 2',
                'marking_scheme' => '+2 marks per correct answer, -0.50 per wrong answer',
                'sections' => [
                    ['name' => 'Quantitative Aptitude', 'questions' => 25, 'marks' => 50],
                    ['name' => 'General Intelligence & Reasoning', 'questions' => 25, 'marks' => 50],
                    ['name' => 'General Awareness', 'questions' => 25, 'marks' => 50],
                    ['name' => 'English Comprehension', 'questions' => 25, 'marks' => 50],
                ],
            ],
        ]);

        $sscMath = Subject::where('exam_id', $ssc->id)->where('name', 'Mathematics')->first();
        $sscReason = Subject::where('exam_id', $ssc->id)->where('name', 'Reasoning')->first();
        $sscGk = Subject::where('exam_id', $ssc->id)->where('name', 'General Knowledge')->first();
        $sscEng = Subject::where('exam_id', $ssc->id)->where('name', 'English')->first();

        ExamSection::create(['exam_pattern_id' => $sscTier1->id, 'name' => 'Quantitative Aptitude', 'slug' => 'quant-apt', 'subject_id' => $sscMath->id, 'total_questions' => 25, 'total_marks' => 50, 'marks_per_question' => 2, 'negative_marks_per_question' => 0.50, 'difficulty_range' => 'easy-hard', 'is_mandatory' => true, 'order' => 1]);
        ExamSection::create(['exam_pattern_id' => $sscTier1->id, 'name' => 'General Intelligence & Reasoning', 'slug' => 'reasoning', 'subject_id' => $sscReason->id, 'total_questions' => 25, 'total_marks' => 50, 'marks_per_question' => 2, 'negative_marks_per_question' => 0.50, 'difficulty_range' => 'easy-hard', 'is_mandatory' => true, 'order' => 2]);
        ExamSection::create(['exam_pattern_id' => $sscTier1->id, 'name' => 'General Awareness', 'slug' => 'gk', 'subject_id' => $sscGk->id, 'total_questions' => 25, 'total_marks' => 50, 'marks_per_question' => 2, 'negative_marks_per_question' => 0.50, 'difficulty_range' => 'easy-hard', 'is_mandatory' => true, 'order' => 3]);
        ExamSection::create(['exam_pattern_id' => $sscTier1->id, 'name' => 'English Comprehension', 'slug' => 'english', 'subject_id' => $sscEng->id, 'total_questions' => 25, 'total_marks' => 50, 'marks_per_question' => 2, 'negative_marks_per_question' => 0.50, 'difficulty_range' => 'easy-hard', 'is_mandatory' => true, 'order' => 4]);

        // ==================== BANKING PO PRELIMS ====================
        $banking = Exam::where('slug', 'banking')->first();
        $bankPrelims = ExamPattern::create([
            'exam_id' => $banking->id,
            'name' => 'IBPS PO Prelims',
            'slug' => 'ibps-po-prelims',
            'description' => 'IBPS PO Preliminary Examination. 100 questions, 60 minutes, 100 marks.',
            'duration_minutes' => 60,
            'total_marks' => 100,
            'total_questions' => 100,
            'sections_count' => 3,
            'negative_marking' => true,
            'negative_marking_value' => 0.25,
            'is_official' => true,
            'order' => 1,
            'details' => [
                'sectional_timing' => true,
                'section_durations' => ['Quantitative Aptitude' => 20, 'Reasoning Ability' => 20, 'English Language' => 20],
                'marking_scheme' => '+1 per correct, -0.25 per wrong',
                'sections' => [
                    ['name' => 'Quantitative Aptitude', 'questions' => 35, 'marks' => 35],
                    ['name' => 'Reasoning Ability', 'questions' => 35, 'marks' => 35],
                    ['name' => 'English Language', 'questions' => 30, 'marks' => 30],
                ],
            ],
        ]);

        $bankQuant = Subject::where('exam_id', $banking->id)->where('name', 'Quantitative Aptitude')->first();
        $bankReason = Subject::where('exam_id', $banking->id)->where('name', 'Reasoning')->first();
        $bankEng = Subject::where('exam_id', $banking->id)->where('name', 'English Language')->first();

        ExamSection::create(['exam_pattern_id' => $bankPrelims->id, 'name' => 'Quantitative Aptitude', 'slug' => 'quant', 'subject_id' => $bankQuant->id, 'total_questions' => 35, 'total_marks' => 35, 'duration_minutes' => 20, 'marks_per_question' => 1, 'negative_marks_per_question' => 0.25, 'difficulty_range' => 'easy-medium', 'is_mandatory' => true, 'order' => 1]);
        ExamSection::create(['exam_pattern_id' => $bankPrelims->id, 'name' => 'Reasoning Ability', 'slug' => 'reasoning', 'subject_id' => $bankReason->id, 'total_questions' => 35, 'total_marks' => 35, 'duration_minutes' => 20, 'marks_per_question' => 1, 'negative_marks_per_question' => 0.25, 'difficulty_range' => 'easy-medium', 'is_mandatory' => true, 'order' => 2]);
        ExamSection::create(['exam_pattern_id' => $bankPrelims->id, 'name' => 'English Language', 'slug' => 'english', 'subject_id' => $bankEng->id, 'total_questions' => 30, 'total_marks' => 30, 'duration_minutes' => 20, 'marks_per_question' => 1, 'negative_marks_per_question' => 0.25, 'difficulty_range' => 'easy-medium', 'is_mandatory' => true, 'order' => 3]);

        // ==================== NDA ====================
        $nda = Exam::where('slug', 'nda')->first();
        $ndaPattern = ExamPattern::create([
            'exam_id' => $nda->id,
            'name' => 'NDA Written Exam',
            'slug' => 'nda-written',
            'description' => 'NDA & NA Written Examination by UPSC. Two papers: Mathematics (300 marks, 120Q) and GAT (600 marks, 150Q).',
            'duration_minutes' => 270,
            'total_marks' => 900,
            'total_questions' => 270,
            'sections_count' => 2,
            'negative_marking' => true,
            'negative_marking_value' => 1.33,
            'is_official' => true,
            'order' => 1,
            'details' => [
                'paper_1' => ['name' => 'Mathematics', 'questions' => 120, 'marks' => 300, 'duration_minutes' => 150],
                'paper_2' => ['name' => 'General Ability Test', 'questions' => 150, 'marks' => 600, 'duration_minutes' => 120],
                'marking_scheme' => '+2.5 marks per correct (Math, -0.83 neg), +4 marks per correct (GAT, -1.33 neg)',
                'sections' => [
                    ['name' => 'Mathematics', 'questions' => 120, 'marks' => 300],
                    ['name' => 'General Ability Test', 'questions' => 150, 'marks' => 600],
                ],
            ],
        ]);

        $ndaMath = Subject::where('exam_id', $nda->id)->where('name', 'Mathematics')->first();
        $ndaGAT = Subject::where('exam_id', $nda->id)->where('name', 'General Ability Test')->first();

        ExamSection::create(['exam_pattern_id' => $ndaPattern->id, 'name' => 'Mathematics', 'slug' => 'math', 'subject_id' => $ndaMath->id, 'total_questions' => 120, 'total_marks' => 300, 'duration_minutes' => 150, 'marks_per_question' => 2.5, 'negative_marks_per_question' => 0.83, 'difficulty_range' => 'easy-hard', 'is_mandatory' => true, 'order' => 1]);
        ExamSection::create(['exam_pattern_id' => $ndaPattern->id, 'name' => 'General Ability Test', 'slug' => 'gat', 'subject_id' => $ndaGAT->id, 'total_questions' => 150, 'total_marks' => 600, 'duration_minutes' => 120, 'marks_per_question' => 4, 'negative_marks_per_question' => 1.33, 'difficulty_range' => 'easy-hard', 'is_mandatory' => true, 'order' => 2]);

        // ==================== RAILWAY RRB CBT 1 ====================
        $railway = Exam::where('slug', 'railway')->first();
        $railCBT1 = ExamPattern::create([
            'exam_id' => $railway->id,
            'name' => 'RRB CBT Stage 1',
            'slug' => 'rrb-cbt1',
            'description' => 'Railway Recruitment Board CBT Stage 1. 100 questions, 90 minutes, 100 marks.',
            'duration_minutes' => 90,
            'total_marks' => 100,
            'total_questions' => 100,
            'sections_count' => 3,
            'negative_marking' => true,
            'negative_marking_value' => 0.33,
            'is_official' => true,
            'order' => 1,
            'details' => [
                'marking_scheme' => '+1 per correct, -1/3 per wrong (1 mark deducted)',
                'sections' => [
                    ['name' => 'Mathematics', 'questions' => 30, 'marks' => 30],
                    ['name' => 'General Intelligence & Reasoning', 'questions' => 30, 'marks' => 30],
                    ['name' => 'General Awareness', 'questions' => 40, 'marks' => 40],
                ],
            ],
        ]);

        $railMath = Subject::where('exam_id', $railway->id)->where('name', 'Mathematics')->first();
        $railReason = Subject::where('exam_id', $railway->id)->where('name', 'General Intelligence')->first();
        $railGK = Subject::where('exam_id', $railway->id)->where('name', 'General Awareness')->first();

        ExamSection::create(['exam_pattern_id' => $railCBT1->id, 'name' => 'Mathematics', 'slug' => 'math', 'subject_id' => $railMath->id, 'total_questions' => 30, 'total_marks' => 30, 'marks_per_question' => 1, 'negative_marks_per_question' => 0.33, 'difficulty_range' => 'easy-medium', 'is_mandatory' => true, 'order' => 1]);
        ExamSection::create(['exam_pattern_id' => $railCBT1->id, 'name' => 'General Intelligence & Reasoning', 'slug' => 'reasoning', 'subject_id' => $railReason->id, 'total_questions' => 30, 'total_marks' => 30, 'marks_per_question' => 1, 'negative_marks_per_question' => 0.33, 'difficulty_range' => 'easy-medium', 'is_mandatory' => true, 'order' => 2]);
        ExamSection::create(['exam_pattern_id' => $railCBT1->id, 'name' => 'General Awareness', 'slug' => 'gk', 'subject_id' => $railGK->id, 'total_questions' => 40, 'total_marks' => 40, 'marks_per_question' => 1, 'negative_marks_per_question' => 0.33, 'difficulty_range' => 'easy-hard', 'is_mandatory' => true, 'order' => 3]);

        // ==================== POLICE ====================
        $police = Exam::where('slug', 'police')->first();
        $polPattern = ExamPattern::create([
            'exam_id' => $police->id,
            'name' => 'Police Constable Written Exam',
            'slug' => 'police-constable',
            'description' => 'Police Constable Written Examination. 100 questions, 120 minutes, 100 marks.',
            'duration_minutes' => 120,
            'total_marks' => 100,
            'total_questions' => 100,
            'sections_count' => 3,
            'negative_marking' => true,
            'negative_marking_value' => 0.25,
            'is_official' => true,
            'order' => 1,
            'details' => [
                'marking_scheme' => '+1 per correct, -0.25 per wrong',
                'sections' => [
                    ['name' => 'Reasoning', 'questions' => 35, 'marks' => 35],
                    ['name' => 'General Knowledge', 'questions' => 35, 'marks' => 35],
                    ['name' => 'Mathematics', 'questions' => 30, 'marks' => 30],
                ],
            ],
        ]);

        $polReason = Subject::where('exam_id', $police->id)->where('name', 'Reasoning')->first();
        $polGK = Subject::where('exam_id', $police->id)->where('name', 'General Knowledge')->first();
        $polMath = Subject::where('exam_id', $police->id)->where('name', 'Mathematics')->first();

        ExamSection::create(['exam_pattern_id' => $polPattern->id, 'name' => 'Reasoning', 'slug' => 'reasoning', 'subject_id' => $polReason->id, 'total_questions' => 35, 'total_marks' => 35, 'marks_per_question' => 1, 'negative_marks_per_question' => 0.25, 'difficulty_range' => 'easy-medium', 'is_mandatory' => true, 'order' => 1]);
        ExamSection::create(['exam_pattern_id' => $polPattern->id, 'name' => 'General Knowledge', 'slug' => 'gk', 'subject_id' => $polGK->id, 'total_questions' => 35, 'total_marks' => 35, 'marks_per_question' => 1, 'negative_marks_per_question' => 0.25, 'difficulty_range' => 'easy-hard', 'is_mandatory' => true, 'order' => 2]);
        ExamSection::create(['exam_pattern_id' => $polPattern->id, 'name' => 'Mathematics', 'slug' => 'math', 'subject_id' => $polMath->id, 'total_questions' => 30, 'total_marks' => 30, 'marks_per_question' => 1, 'negative_marks_per_question' => 0.25, 'difficulty_range' => 'easy-medium', 'is_mandatory' => true, 'order' => 3]);

        // ==================== STATE PSC PRELIMS ====================
        $psc = Exam::where('slug', 'state-psc')->first();
        $pscPrelims = ExamPattern::create([
            'exam_id' => $psc->id,
            'name' => 'State PSC Prelims',
            'slug' => 'state-psc-prelims',
            'description' => 'State Public Service Commission Preliminary Examination. 200 questions, 180 minutes, 200 marks.',
            'duration_minutes' => 180,
            'total_marks' => 200,
            'total_questions' => 200,
            'sections_count' => 2,
            'negative_marking' => true,
            'negative_marking_value' => 0.33,
            'is_official' => true,
            'order' => 1,
            'details' => [
                'paper_1' => ['name' => 'General Studies', 'questions' => 100, 'marks' => 100],
                'paper_2' => ['name' => 'Aptitude Test', 'questions' => 100, 'marks' => 100],
                'marking_scheme' => '+1 per correct, -0.33 per wrong',
                'sections' => [
                    ['name' => 'General Studies', 'questions' => 100, 'marks' => 100],
                    ['name' => 'Aptitude Test', 'questions' => 100, 'marks' => 100],
                ],
            ],
        ]);

        $pscGS = Subject::where('exam_id', $psc->id)->where('name', 'General Studies')->first();
        $pscMath = Subject::where('exam_id', $psc->id)->where('name', 'Mathematics')->first();

        ExamSection::create(['exam_pattern_id' => $pscPrelims->id, 'name' => 'General Studies', 'slug' => 'gs', 'subject_id' => $pscGS->id, 'total_questions' => 100, 'total_marks' => 100, 'marks_per_question' => 1, 'negative_marks_per_question' => 0.33, 'difficulty_range' => 'easy-hard', 'is_mandatory' => true, 'order' => 1]);
        ExamSection::create(['exam_pattern_id' => $pscPrelims->id, 'name' => 'Aptitude Test', 'slug' => 'aptitude', 'subject_id' => $pscMath->id, 'total_questions' => 100, 'total_marks' => 100, 'marks_per_question' => 1, 'negative_marks_per_question' => 0.33, 'difficulty_range' => 'easy-hard', 'is_mandatory' => true, 'order' => 2]);
    }

    private function seedExamsAndContent(): void
    {
        // ==================== SSC ====================
        $ssc = Exam::create(['name' => 'SSC', 'slug' => 'ssc', 'description' => 'Staff Selection Commission', 'order' => 1]);

        $sscMath = Subject::create(['exam_id' => $ssc->id, 'name' => 'Mathematics', 'slug' => 'ssc-math', 'order' => 1]);
        $sscEnglish = Subject::create(['exam_id' => $ssc->id, 'name' => 'English', 'slug' => 'ssc-english', 'order' => 2]);
        $sscReasoning = Subject::create(['exam_id' => $ssc->id, 'name' => 'Reasoning', 'slug' => 'ssc-reasoning', 'order' => 3]);
        $sscGk = Subject::create(['exam_id' => $ssc->id, 'name' => 'General Knowledge', 'slug' => 'ssc-gk', 'order' => 4]);

        // SSC Math Topics
        $sscAlg = Topic::create(['subject_id' => $sscMath->id, 'name' => 'Algebra', 'slug' => 'ssc-algebra', 'order' => 1]);
        $sscGeo = Topic::create(['subject_id' => $sscMath->id, 'name' => 'Geometry', 'slug' => 'ssc-geometry', 'order' => 2]);
        $sscArith = Topic::create(['subject_id' => $sscMath->id, 'name' => 'Arithmetic', 'slug' => 'ssc-arithmetic', 'order' => 3]);
        $sscTrig = Topic::create(['subject_id' => $sscMath->id, 'name' => 'Trigonometry', 'slug' => 'ssc-trigonometry', 'order' => 4]);

        // SSC English Topics
        $sscGram = Topic::create(['subject_id' => $sscEnglish->id, 'name' => 'Grammar', 'slug' => 'ssc-grammar', 'order' => 1]);
        $sscVocab = Topic::create(['subject_id' => $sscEnglish->id, 'name' => 'Vocabulary', 'slug' => 'ssc-vocabulary', 'order' => 2]);
        $sscComp = Topic::create(['subject_id' => $sscEnglish->id, 'name' => 'Comprehension', 'slug' => 'ssc-comprehension', 'order' => 3]);

        // SSC Reasoning Topics
        $sscVerbal = Topic::create(['subject_id' => $sscReasoning->id, 'name' => 'Verbal Reasoning', 'slug' => 'ssc-verbal', 'order' => 1]);
        $sscNonVerbal = Topic::create(['subject_id' => $sscReasoning->id, 'name' => 'Non-Verbal Reasoning', 'slug' => 'ssc-nonverbal', 'order' => 2]);

        // SSC GK Topics
        $sscCurrent = Topic::create(['subject_id' => $sscGk->id, 'name' => 'Current Affairs', 'slug' => 'ssc-current', 'order' => 1]);
        $sscStatic = Topic::create(['subject_id' => $sscGk->id, 'name' => 'Static GK', 'slug' => 'ssc-static', 'order' => 2]);

        // SSC Questions
        $this->createQuestions($ssc->id, $sscMath->id, $sscAlg->id, $this->sscAlgebraQuestions());
        $this->createQuestions($ssc->id, $sscMath->id, $sscGeo->id, $this->sscGeometryQuestions());
        $this->createQuestions($ssc->id, $sscMath->id, $sscArith->id, $this->sscArithmeticQuestions());
        $this->createQuestions($ssc->id, $sscMath->id, $sscTrig->id, $this->sscTrigonometryQuestions());
        $this->createQuestions($ssc->id, $sscEnglish->id, $sscGram->id, $this->sscGrammarQuestions());
        $this->createQuestions($ssc->id, $sscEnglish->id, $sscVocab->id, $this->sscVocabularyQuestions());
        $this->createQuestions($ssc->id, $sscReasoning->id, $sscVerbal->id, $this->sscVerbalReasoningQuestions());
        $this->createQuestions($ssc->id, $sscGk->id, $sscCurrent->id, $this->sscCurrentAffairsQuestions());
        $this->createQuestions($ssc->id, $sscGk->id, $sscStatic->id, $this->sscStaticGKQuestions());

        // ==================== BANKING ====================
        $banking = Exam::create(['name' => 'Banking', 'slug' => 'banking', 'description' => 'Banking Exams (IBPS, SBI, RBI)', 'order' => 2]);

        $bankQuant = Subject::create(['exam_id' => $banking->id, 'name' => 'Quantitative Aptitude', 'slug' => 'bank-quant', 'order' => 1]);
        $bankReason = Subject::create(['exam_id' => $banking->id, 'name' => 'Reasoning', 'slug' => 'bank-reasoning', 'order' => 2]);
        $bankEng = Subject::create(['exam_id' => $banking->id, 'name' => 'English Language', 'slug' => 'bank-english', 'order' => 3]);
        $bankGA = Subject::create(['exam_id' => $banking->id, 'name' => 'General Awareness', 'slug' => 'bank-ga', 'order' => 4]);

        $bankPerc = Topic::create(['subject_id' => $bankQuant->id, 'name' => 'Percentage', 'slug' => 'bank-percentage', 'order' => 1]);
        $bankProfi = Topic::create(['subject_id' => $bankQuant->id, 'name' => 'Profit & Loss', 'slug' => 'bank-profit-loss', 'order' => 2]);
        $bankSI = Topic::create(['subject_id' => $bankQuant->id, 'name' => 'Simple Interest', 'slug' => 'bank-si', 'order' => 3]);
        $bankPuzzle = Topic::create(['subject_id' => $bankReason->id, 'name' => 'Puzzles', 'slug' => 'bank-puzzles', 'order' => 1]);
        $bankSyllog = Topic::create(['subject_id' => $bankReason->id, 'name' => 'Syllogism', 'slug' => 'bank-syllogism', 'order' => 2]);
        $bankClose = Topic::create(['subject_id' => $bankEng->id, 'name' => 'Cloze Test', 'slug' => 'bank-cloze', 'order' => 1]);
        $bankBanking = Topic::create(['subject_id' => $bankGA->id, 'name' => 'Banking Awareness', 'slug' => 'bank-banking', 'order' => 1]);
        $bankEco = Topic::create(['subject_id' => $bankGA->id, 'name' => 'Economy', 'slug' => 'bank-economy', 'order' => 2]);

        $this->createQuestions($banking->id, $bankQuant->id, $bankPerc->id, $this->bankPercentageQuestions());
        $this->createQuestions($banking->id, $bankQuant->id, $bankProfi->id, $this->bankProfitLossQuestions());
        $this->createQuestions($banking->id, $bankQuant->id, $bankSI->id, $this->bankSIQuestions());
        $this->createQuestions($banking->id, $bankReason->id, $bankPuzzle->id, $this->bankPuzzleQuestions());
        $this->createQuestions($banking->id, $bankReason->id, $bankSyllog->id, $this->bankSyllogismQuestions());
        $this->createQuestions($banking->id, $bankEng->id, $bankClose->id, $this->bankClozeQuestions());
        $this->createQuestions($banking->id, $bankGA->id, $bankBanking->id, $this->bankBankingQuestions());
        $this->createQuestions($banking->id, $bankGA->id, $bankEco->id, $this->bankEconomyQuestions());

        // ==================== NDA ====================
        $nda = Exam::create(['name' => 'NDA', 'slug' => 'nda', 'description' => 'National Defence Academy', 'order' => 3]);

        $ndaMath = Subject::create(['exam_id' => $nda->id, 'name' => 'Mathematics', 'slug' => 'nda-math', 'order' => 1]);
        $ndaGK = Subject::create(['exam_id' => $nda->id, 'name' => 'General Ability Test', 'slug' => 'nda-gat', 'order' => 2]);
        $ndaEng = Subject::create(['exam_id' => $nda->id, 'name' => 'English', 'slug' => 'nda-english', 'order' => 3]);

        $ndaCalc = Topic::create(['subject_id' => $ndaMath->id, 'name' => 'Calculus', 'slug' => 'nda-calculus', 'order' => 1]);
        $ndaAlgebra = Topic::create(['subject_id' => $ndaMath->id, 'name' => 'Algebra', 'slug' => 'nda-algebra', 'order' => 2]);
        $ndaPhysics = Topic::create(['subject_id' => $ndaGK->id, 'name' => 'Physics', 'slug' => 'nda-physics', 'order' => 1]);
        $ndaChem = Topic::create(['subject_id' => $ndaGK->id, 'name' => 'Chemistry', 'slug' => 'nda-chemistry', 'order' => 2]);
        $ndaHistory = Topic::create(['subject_id' => $ndaGK->id, 'name' => 'History', 'slug' => 'nda-history', 'order' => 3]);
        $ndaEngGram = Topic::create(['subject_id' => $ndaEng->id, 'name' => 'Grammar', 'slug' => 'nda-grammar', 'order' => 1]);

        $this->createQuestions($nda->id, $ndaMath->id, $ndaCalc->id, $this->ndaCalculusQuestions());
        $this->createQuestions($nda->id, $ndaMath->id, $ndaAlgebra->id, $this->ndaAlgebraQuestions());
        $this->createQuestions($nda->id, $ndaGK->id, $ndaPhysics->id, $this->ndaPhysicsQuestions());
        $this->createQuestions($nda->id, $ndaGK->id, $ndaChem->id, $this->ndaChemistryQuestions());
        $this->createQuestions($nda->id, $ndaGK->id, $ndaHistory->id, $this->ndaHistoryQuestions());
        $this->createQuestions($nda->id, $ndaEng->id, $ndaEngGram->id, $this->ndaGrammarQuestions());

        // ==================== RAILWAY ====================
        $railway = Exam::create(['name' => 'Railway', 'slug' => 'railway', 'description' => 'Railway Recruitment (RRB)', 'order' => 4]);

        $railMath = Subject::create(['exam_id' => $railway->id, 'name' => 'Mathematics', 'slug' => 'rail-math', 'order' => 1]);
        $railReason = Subject::create(['exam_id' => $railway->id, 'name' => 'General Intelligence', 'slug' => 'rail-reasoning', 'order' => 2]);
        $railGK = Subject::create(['exam_id' => $railway->id, 'name' => 'General Awareness', 'slug' => 'rail-gk', 'order' => 3]);

        $railNumber = Topic::create(['subject_id' => $railMath->id, 'name' => 'Number System', 'slug' => 'rail-number', 'order' => 1]);
        $railRatio = Topic::create(['subject_id' => $railMath->id, 'name' => 'Ratio & Proportion', 'slug' => 'rail-ratio', 'order' => 2]);
        $railAnalogy = Topic::create(['subject_id' => $railReason->id, 'name' => 'Analogy', 'slug' => 'rail-analogy', 'order' => 1]);
        $railCurrent = Topic::create(['subject_id' => $railGK->id, 'name' => 'Current Affairs', 'slug' => 'rail-current', 'order' => 1]);
        $railGeo = Topic::create(['subject_id' => $railGK->id, 'name' => 'Geography', 'slug' => 'rail-geography', 'order' => 2]);

        $this->createQuestions($railway->id, $railMath->id, $railNumber->id, $this->railNumberQuestions());
        $this->createQuestions($railway->id, $railMath->id, $railRatio->id, $this->railRatioQuestions());
        $this->createQuestions($railway->id, $railReason->id, $railAnalogy->id, $this->railAnalogyQuestions());
        $this->createQuestions($railway->id, $railGK->id, $railCurrent->id, $this->railCurrentQuestions());
        $this->createQuestions($railway->id, $railGK->id, $railGeo->id, $this->railGeographyQuestions());

        // ==================== POLICE ====================
        $police = Exam::create(['name' => 'Police', 'slug' => 'police', 'description' => 'Police Recruitment Exams', 'order' => 5]);

        $polReason = Subject::create(['exam_id' => $police->id, 'name' => 'Reasoning', 'slug' => 'pol-reasoning', 'order' => 1]);
        $polGK = Subject::create(['exam_id' => $police->id, 'name' => 'General Knowledge', 'slug' => 'pol-gk', 'order' => 2]);
        $polMath = Subject::create(['exam_id' => $police->id, 'name' => 'Mathematics', 'slug' => 'pol-math', 'order' => 3]);

        $polCoding = Topic::create(['subject_id' => $polReason->id, 'name' => 'Coding-Decoding', 'slug' => 'pol-coding', 'order' => 1]);
        $polBlood = Topic::create(['subject_id' => $polReason->id, 'name' => 'Blood Relations', 'slug' => 'pol-blood', 'order' => 2]);
        $polIPC = Topic::create(['subject_id' => $polGK->id, 'name' => 'Indian Penal Code', 'slug' => 'pol-ipc', 'order' => 1]);
        $polConstit = Topic::create(['subject_id' => $polGK->id, 'name' => 'Constitution', 'slug' => 'pol-constitution', 'order' => 2]);
        $polArith = Topic::create(['subject_id' => $polMath->id, 'name' => 'Arithmetic', 'slug' => 'pol-arithmetic', 'order' => 1]);

        $this->createQuestions($police->id, $polReason->id, $polCoding->id, $this->polCodingQuestions());
        $this->createQuestions($police->id, $polReason->id, $polBlood->id, $this->polBloodQuestions());
        $this->createQuestions($police->id, $polGK->id, $polIPC->id, $this->polIPCQuestions());
        $this->createQuestions($police->id, $polGK->id, $polConstit->id, $this->polConstitutionQuestions());
        $this->createQuestions($police->id, $polMath->id, $polArith->id, $this->polArithmeticQuestions());

        // ==================== STATE PSC ====================
        $psc = Exam::create(['name' => 'State PSC', 'slug' => 'state-psc', 'description' => 'State Public Service Commission', 'order' => 6]);

        $pscGK = Subject::create(['exam_id' => $psc->id, 'name' => 'General Studies', 'slug' => 'psc-gs', 'order' => 1]);
        $pscMath = Subject::create(['exam_id' => $psc->id, 'name' => 'Mathematics', 'slug' => 'psc-math', 'order' => 2]);
        $pscReason = Subject::create(['exam_id' => $psc->id, 'name' => 'Reasoning', 'slug' => 'psc-reasoning', 'order' => 3]);

        $pscIndian = Topic::create(['subject_id' => $pscGK->id, 'name' => 'Indian History', 'slug' => 'psc-history', 'order' => 1]);
        $pscPolity = Topic::create(['subject_id' => $pscGK->id, 'name' => 'Indian Polity', 'slug' => 'psc-polity', 'order' => 2]);
        $pscGeography = Topic::create(['subject_id' => $pscGK->id, 'name' => 'Geography', 'slug' => 'psc-geography', 'order' => 3]);
        $pscAptitude = Topic::create(['subject_id' => $pscMath->id, 'name' => 'Quantitative Aptitude', 'slug' => 'psc-aptitude', 'order' => 1]);
        $pscLogic = Topic::create(['subject_id' => $pscReason->id, 'name' => 'Logical Reasoning', 'slug' => 'psc-logic', 'order' => 1]);

        $this->createQuestions($psc->id, $pscGK->id, $pscIndian->id, $this->pscHistoryQuestions());
        $this->createQuestions($psc->id, $pscGK->id, $pscPolity->id, $this->pscPolityQuestions());
        $this->createQuestions($psc->id, $pscGK->id, $pscGeography->id, $this->pscGeographyQuestions());
        $this->createQuestions($psc->id, $pscMath->id, $pscAptitude->id, $this->pscAptitudeQuestions());
        $this->createQuestions($psc->id, $pscReason->id, $pscLogic->id, $this->pscLogicQuestions());
    }

    private function createQuestions(int $examId, int $subjectId, int $topicId, array $questions): void
    {
        foreach ($questions as $q) {
            $question = Question::create([
                'exam_id' => $examId,
                'subject_id' => $subjectId,
                'topic_id' => $topicId,
                'question_text' => $q['question'],
                'question_type' => 'mcq',
                'difficulty' => $q['difficulty'],
                'explanation' => $q['explanation'] ?? null,
                'status' => 'approved',
            ]);

            foreach ($q['options'] as $index => $opt) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'option_text' => $opt['text'],
                    'is_correct' => $opt['correct'],
                    'order' => $index + 1,
                ]);
            }
        }
    }

    // ==================== SSC QUESTIONS ====================

    private function sscAlgebraQuestions(): array
    {
        return [
            ['question' => 'If x + y = 10 and xy = 21, find x² + y².', 'difficulty' => 'medium', 'explanation' => '(x + y)² = x² + y² + 2xy. So 100 = x² + y² + 42. Therefore x² + y² = 58.', 'options' => [['text' => '58', 'correct' => true], ['text' => '52', 'correct' => false], ['text' => '62', 'correct' => false], ['text' => '48', 'correct' => false]]],
            ['question' => 'Solve: 2x² - 5x + 3 = 0', 'difficulty' => 'easy', 'explanation' => '(2x - 3)(x - 1) = 0. So x = 3/2 or x = 1.', 'options' => [['text' => 'x = 1, x = 3/2', 'correct' => true], ['text' => 'x = 2, x = 3', 'correct' => false], ['text' => 'x = -1, x = -3/2', 'correct' => false], ['text' => 'x = 1/2, x = 3', 'correct' => false]]],
            ['question' => 'If a² + b² = 25 and ab = 12, find (a + b)².', 'difficulty' => 'medium', 'explanation' => '(a + b)² = a² + b² + 2ab = 25 + 24 = 49.', 'options' => [['text' => '49', 'correct' => true], ['text' => '25', 'correct' => false], ['text' => '36', 'correct' => false], ['text' => '61', 'correct' => false]]],
            ['question' => 'If x² + 1/x² = 7, find x³ + 1/x³.', 'difficulty' => 'hard', 'explanation' => 'x + 1/x = √(7+2) = 3. x³ + 1/x³ = (x+1/x)³ - 3(x+1/x) = 27 - 9 = 18.', 'options' => [['text' => '18', 'correct' => true], ['text' => '21', 'correct' => false], ['text' => '27', 'correct' => false], ['text' => '36', 'correct' => false]]],
            ['question' => 'The value of (a + b)³ is:', 'difficulty' => 'easy', 'explanation' => '(a + b)³ = a³ + b³ + 3ab(a + b).', 'options' => [['text' => 'a³ + b³ + 3ab(a + b)', 'correct' => true], ['text' => 'a³ + b³ + 3a²b', 'correct' => false], ['text' => 'a³ + b³ + ab(a + b)', 'correct' => false], ['text' => 'a³ + b³ + 3ab', 'correct' => false]]],
            ['question' => 'If x + 1/x = 5, find x² + 1/x².', 'difficulty' => 'medium', 'explanation' => 'x² + 1/x² = (x + 1/x)² - 2 = 25 - 2 = 23.', 'options' => [['text' => '23', 'correct' => true], ['text' => '25', 'correct' => false], ['text' => '27', 'correct' => false], ['text' => '21', 'correct' => false]]],
            ['question' => 'Factorize: x² - 5x + 6', 'difficulty' => 'easy', 'explanation' => 'x² - 5x + 6 = (x - 2)(x - 3).', 'options' => [['text' => '(x - 2)(x - 3)', 'correct' => true], ['text' => '(x + 2)(x + 3)', 'correct' => false], ['text' => '(x - 1)(x - 6)', 'correct' => false], ['text' => '(x + 1)(x + 6)', 'correct' => false]]],
            ['question' => 'If 3x + 2y = 12 and xy = 6, find 9x² + 4y².', 'difficulty' => 'hard', 'explanation' => '(3x + 2y)² = 9x² + 4y² + 12xy. 144 = 9x² + 4y² + 72. So 9x² + 4y² = 72.', 'options' => [['text' => '72', 'correct' => true], ['text' => '144', 'correct' => false], ['text' => '36', 'correct' => false], ['text' => '108', 'correct' => false]]],
        ];
    }

    private function sscGeometryQuestions(): array
    {
        return [
            ['question' => 'What is the area of a circle with radius 7 cm? (Take π = 22/7)', 'difficulty' => 'easy', 'explanation' => 'Area = πr² = (22/7) × 7 × 7 = 154 cm².', 'options' => [['text' => '154 cm²', 'correct' => true], ['text' => '144 cm²', 'correct' => false], ['text' => '166 cm²', 'correct' => false], ['text' => '132 cm²', 'correct' => false]]],
            ['question' => 'The perimeter of a square is 48 cm. What is its area?', 'difficulty' => 'easy', 'explanation' => 'Side = 48/4 = 12 cm. Area = 12² = 144 cm².', 'options' => [['text' => '144 cm²', 'correct' => true], ['text' => '196 cm²', 'correct' => false], ['text' => '121 cm²', 'correct' => false], ['text' => '169 cm²', 'correct' => false]]],
            ['question' => 'In a right triangle, if one leg is 5 cm and hypotenuse is 13 cm, find the other leg.', 'difficulty' => 'medium', 'explanation' => 'By Pythagoras: a² + 5² = 13². a² = 169 - 25 = 144. a = 12 cm.', 'options' => [['text' => '12 cm', 'correct' => true], ['text' => '10 cm', 'correct' => false], ['text' => '8 cm', 'correct' => false], ['text' => '15 cm', 'correct' => false]]],
            ['question' => 'The diagonals of a rhombus are 16 cm and 12 cm. Find its area.', 'difficulty' => 'medium', 'explanation' => 'Area = (d1 × d2)/2 = (16 × 12)/2 = 96 cm².', 'options' => [['text' => '96 cm²', 'correct' => true], ['text' => '192 cm²', 'correct' => false], ['text' => '48 cm²', 'correct' => false], ['text' => '72 cm²', 'correct' => false]]],
            ['question' => 'What is the volume of a cylinder with radius 7 cm and height 10 cm? (Take π = 22/7)', 'difficulty' => 'medium', 'explanation' => 'Volume = πr²h = (22/7) × 49 × 10 = 1540 cm³.', 'options' => [['text' => '1540 cm³', 'correct' => true], ['text' => '1440 cm³', 'correct' => false], ['text' => '1660 cm³', 'correct' => false], ['text' => '1320 cm³', 'correct' => false]]],
            ['question' => 'The ratio of the circumference to the diameter of a circle is:', 'difficulty' => 'easy', 'explanation' => 'Circumference/Diameter = πr²/2r = π.', 'options' => [['text' => 'π', 'correct' => true], ['text' => '2π', 'correct' => false], ['text' => 'π/2', 'correct' => false], ['text' => 'π²', 'correct' => false]]],
        ];
    }

    private function sscArithmeticQuestions(): array
    {
        return [
            ['question' => 'A shopkeeper buys an article for ₹400 and sells it for ₹500. What is the profit percentage?', 'difficulty' => 'easy', 'explanation' => 'Profit = 500 - 400 = 100. Profit% = (100/400) × 100 = 25%.', 'options' => [['text' => '25%', 'correct' => true], ['text' => '20%', 'correct' => false], ['text' => '30%', 'correct' => false], ['text' => '15%', 'correct' => false]]],
            ['question' => 'If a number is increased by 20% and then decreased by 20%, what is the net change?', 'difficulty' => 'medium', 'explanation' => 'Net change = -20²/100 = -4%. So 4% decrease.', 'options' => [['text' => '4% decrease', 'correct' => true], ['text' => 'No change', 'correct' => false], ['text' => '4% increase', 'correct' => false], ['text' => '2% decrease', 'correct' => false]]],
            ['question' => 'What is 15% of 200?', 'difficulty' => 'easy', 'explanation' => '15% of 200 = (15/100) × 200 = 30.', 'options' => [['text' => '30', 'correct' => true], ['text' => '25', 'correct' => false], ['text' => '35', 'correct' => false], ['text' => '20', 'correct' => false]]],
            ['question' => 'A train travels 360 km in 4 hours. What is its speed in m/s?', 'difficulty' => 'medium', 'explanation' => 'Speed = 360/4 = 90 km/h = 90 × 5/18 = 25 m/s.', 'options' => [['text' => '25 m/s', 'correct' => true], ['text' => '30 m/s', 'correct' => false], ['text' => '20 m/s', 'correct' => false], ['text' => '35 m/s', 'correct' => false]]],
            ['question' => 'If A can do a work in 10 days and B can do it in 15 days, how many days will they take together?', 'difficulty' => 'medium', 'explanation' => 'A\'s rate = 1/10, B\'s rate = 1/15. Combined = 1/10 + 1/15 = 5/30 = 1/6. So 6 days.', 'options' => [['text' => '6 days', 'correct' => true], ['text' => '5 days', 'correct' => false], ['text' => '8 days', 'correct' => false], ['text' => '7 days', 'correct' => false]]],
            ['question' => 'The average of 5 numbers is 20. If one number is excluded, the average becomes 18. What is the excluded number?', 'difficulty' => 'medium', 'explanation' => 'Sum of 5 = 100. Sum of 4 = 72. Excluded = 100 - 72 = 28.', 'options' => [['text' => '28', 'correct' => true], ['text' => '26', 'correct' => false], ['text' => '30', 'correct' => false], ['text' => '24', 'correct' => false]]],
        ];
    }

    private function sscTrigonometryQuestions(): array
    {
        return [
            ['question' => 'What is the value of sin 30°?', 'difficulty' => 'easy', 'explanation' => 'sin 30° = 1/2.', 'options' => [['text' => '1/2', 'correct' => true], ['text' => '√3/2', 'correct' => false], ['text' => '1/√2', 'correct' => false], ['text' => '1', 'correct' => false]]],
            ['question' => 'What is the value of cos 60°?', 'difficulty' => 'easy', 'explanation' => 'cos 60° = 1/2.', 'options' => [['text' => '1/2', 'correct' => true], ['text' => '√3/2', 'correct' => false], ['text' => '1/√2', 'correct' => false], ['text' => '0', 'correct' => false]]],
            ['question' => 'If tan θ = 3/4, find sin θ.', 'difficulty' => 'medium', 'explanation' => 'If tan θ = 3/4, hypotenuse = 5. sin θ = 3/5.', 'options' => [['text' => '3/5', 'correct' => true], ['text' => '4/5', 'correct' => false], ['text' => '3/4', 'correct' => false], ['text' => '5/3', 'correct' => false]]],
            ['question' => 'sin²θ + cos²θ = ?', 'difficulty' => 'easy', 'explanation' => 'This is a fundamental trigonometric identity. sin²θ + cos²θ = 1.', 'options' => [['text' => '1', 'correct' => true], ['text' => '0', 'correct' => false], ['text' => '2', 'correct' => false], ['text' => 'sin 2θ', 'correct' => false]]],
            ['question' => 'What is the value of tan 45°?', 'difficulty' => 'easy', 'explanation' => 'tan 45° = 1.', 'options' => [['text' => '1', 'correct' => true], ['text' => '0', 'correct' => false], ['text' => '√3', 'correct' => false], ['text' => '1/√3', 'correct' => false]]],
        ];
    }

    private function sscGrammarQuestions(): array
    {
        return [
            ['question' => 'Choose the correct synonym of "BENEVOLENT":', 'difficulty' => 'easy', 'explanation' => 'Benevolent means kind and generous.', 'options' => [['text' => 'Kind', 'correct' => true], ['text' => 'Cruel', 'correct' => false], ['text' => 'Strict', 'correct' => false], ['text' => 'Rude', 'correct' => false]]],
            ['question' => 'Choose the correct antonym of "ABUNDANT":', 'difficulty' => 'easy', 'explanation' => 'Abundant means plentiful; scarce is its opposite.', 'options' => [['text' => 'Scarce', 'correct' => true], ['text' => 'Plentiful', 'correct' => false], ['text' => 'Ample', 'correct' => false], ['text' => 'Sufficient', 'correct' => false]]],
            ['question' => 'Identify the error: "Each of the boys have completed their work."', 'difficulty' => 'medium', 'explanation' => '"Each" is singular, so it should be "has completed".', 'options' => [['text' => 'Each...has completed', 'correct' => true], ['text' => 'Each of the boys', 'correct' => false], ['text' => 'their work', 'correct' => false], ['text' => 'No error', 'correct' => false]]],
            ['question' => 'Choose the correct form: "He _____ to school every day."', 'difficulty' => 'easy', 'explanation' => 'Third person singular present tense uses "goes".', 'options' => [['text' => 'goes', 'correct' => true], ['text' => 'go', 'correct' => false], ['text' => 'going', 'correct' => false], ['text' => 'gone', 'correct' => false]]],
            ['question' => 'What is the plural of "child"?', 'difficulty' => 'easy', 'explanation' => 'The plural of child is children (irregular plural).', 'options' => [['text' => 'children', 'correct' => true], ['text' => 'childs', 'correct' => false], ['text' => 'childes', 'correct' => false], ['text' => 'childern', 'correct' => false]]],
            ['question' => 'Choose the correct article: "He is _____ honest man."', 'difficulty' => 'easy', 'explanation' => '"Honest" starts with a vowel sound, so use "an".', 'options' => [['text' => 'an', 'correct' => true], ['text' => 'a', 'correct' => false], ['text' => 'the', 'correct' => false], ['text' => 'no article needed', 'correct' => false]]],
        ];
    }

    private function sscVocabularyQuestions(): array
    {
        return [
            ['question' => 'What does "Ephemeral" mean?', 'difficulty' => 'medium', 'explanation' => 'Ephemeral means lasting for a very short time.', 'options' => [['text' => 'Short-lived', 'correct' => true], ['text' => 'Eternal', 'correct' => false], ['text' => 'Massive', 'correct' => false], ['text' => 'Ancient', 'correct' => false]]],
            ['question' => 'What does "Ubiquitous" mean?', 'difficulty' => 'medium', 'explanation' => 'Ubiquitous means present, appearing, or found everywhere.', 'options' => [['text' => 'Found everywhere', 'correct' => true], ['text' => 'Unique', 'correct' => false], ['text' => 'Rare', 'correct' => false], ['text' => 'Hidden', 'correct' => false]]],
            ['question' => 'Choose the correct meaning of "PRAGMATIC":', 'difficulty' => 'medium', 'explanation' => 'Pragmatic means dealing with things sensibly and realistically.', 'options' => [['text' => 'Practical', 'correct' => true], ['text' => 'Idealistic', 'correct' => false], ['text' => 'Emotional', 'correct' => false], ['text' => 'Theoretical', 'correct' => false]]],
            ['question' => 'What is the synonym of "MELLIFLUOUS"?', 'difficulty' => 'hard', 'explanation' => 'Mellifluous means sweet-sounding; smooth and musical.', 'options' => [['text' => 'Sweet-sounding', 'correct' => true], ['text' => 'Harsh', 'correct' => false], ['text' => 'Loud', 'correct' => false], ['text' => 'Shrill', 'correct' => false]]],
        ];
    }

    private function sscVerbalReasoningQuestions(): array
    {
        return [
            ['question' => 'If COMPUTER is coded as DNPQVUFS, how is SCIENCE coded?', 'difficulty' => 'medium', 'explanation' => 'Each letter is shifted by +1. S→T, C→D, I→J, E→F, N→O, C→D, E→F. So SCIENCE → TDJFO DF.', 'options' => [['text' => 'TDJFODF', 'correct' => true], ['text' => 'TDJFODF', 'correct' => false], ['text' => 'TDJFODE', 'correct' => false], ['text' => 'SCJFODF', 'correct' => false]]],
            ['question' => 'Find the odd one out: 2, 3, 5, 7, 11, 14, 17', 'difficulty' => 'easy', 'explanation' => 'All are prime numbers except 14 (which is 2 × 7).', 'options' => [['text' => '14', 'correct' => true], ['text' => '7', 'correct' => false], ['text' => '11', 'correct' => false], ['text' => '17', 'correct' => false]]],
            ['question' => 'A is the brother of B. C is the daughter of B. How is A related to C?', 'difficulty' => 'easy', 'explanation' => 'A is B\'s brother, so A is C\'s uncle.', 'options' => [['text' => 'Uncle', 'correct' => true], ['text' => 'Father', 'correct' => false], ['text' => 'Brother', 'correct' => false], ['text' => 'Son', 'correct' => false]]],
            ['question' => 'If + means ×, × means -, - means ÷, and ÷ means +, find: 8 + 4 - 2 ÷ 1', 'difficulty' => 'medium', 'explanation' => 'Replace: 8 × 4 ÷ 2 + 1 = 32 ÷ 2 + 1 = 16 + 1 = 17.', 'options' => [['text' => '17', 'correct' => true], ['text' => '15', 'correct' => false], ['text' => '19', 'correct' => false], ['text' => '21', 'correct' => false]]],
        ];
    }

    private function sscCurrentAffairsQuestions(): array
    {
        return [
            ['question' => 'Who is the current President of India (as of 2025)?', 'difficulty' => 'easy', 'explanation' => 'Droupadi Murmu is the current President of India.', 'options' => [['text' => 'Droupadi Murmu', 'correct' => true], ['text' => 'Ram Nath Kovind', 'correct' => false], ['text' => 'Pranab Mukherjee', 'correct' => false], ['text' => 'APJ Abdul Kalam', 'correct' => false]]],
            ['question' => 'Which planet is known as the Red Planet?', 'difficulty' => 'easy', 'explanation' => 'Mars is called the Red Planet due to iron oxide on its surface.', 'options' => [['text' => 'Mars', 'correct' => true], ['text' => 'Venus', 'correct' => false], ['text' => 'Jupiter', 'correct' => false], ['text' => 'Saturn', 'correct' => false]]],
            ['question' => 'What is the currency of Japan?', 'difficulty' => 'easy', 'explanation' => 'The Japanese currency is Yen (¥).', 'options' => [['text' => 'Yen', 'correct' => true], ['text' => 'Won', 'correct' => false], ['text' => 'Yuan', 'correct' => false], ['text' => 'Baht', 'correct' => false]]],
            ['question' => 'Which is the largest ocean in the world?', 'difficulty' => 'easy', 'explanation' => 'The Pacific Ocean is the largest ocean.', 'options' => [['text' => 'Pacific Ocean', 'correct' => true], ['text' => 'Atlantic Ocean', 'correct' => false], ['text' => 'Indian Ocean', 'correct' => false], ['text' => 'Arctic Ocean', 'correct' => false]]],
        ];
    }

    private function sscStaticGKQuestions(): array
    {
        return [
            ['question' => 'What is the capital of France?', 'difficulty' => 'easy', 'explanation' => 'Paris is the capital of France.', 'options' => [['text' => 'Paris', 'correct' => true], ['text' => 'London', 'correct' => false], ['text' => 'Berlin', 'correct' => false], ['text' => 'Rome', 'correct' => false]]],
            ['question' => 'How many planets are there in our solar system?', 'difficulty' => 'easy', 'explanation' => 'There are 8 planets in our solar system.', 'options' => [['text' => '8', 'correct' => true], ['text' => '7', 'correct' => false], ['text' => '9', 'correct' => false], ['text' => '10', 'correct' => false]]],
            ['question' => 'What is the chemical formula for water?', 'difficulty' => 'easy', 'explanation' => 'Water is H₂O (two hydrogen atoms and one oxygen atom).', 'options' => [['text' => 'H₂O', 'correct' => true], ['text' => 'CO₂', 'correct' => false], ['text' => 'NaCl', 'correct' => false], ['text' => 'O₂', 'correct' => false]]],
            ['question' => 'Who wrote the Indian National Anthem?', 'difficulty' => 'easy', 'explanation' => 'Rabindranath Tagore wrote Jana Gana Mana.', 'options' => [['text' => 'Rabindranath Tagore', 'correct' => true], ['text' => 'Bankim Chandra Chatterjee', 'correct' => false], ['text' => 'Mahatma Gandhi', 'correct' => false], ['text' => 'Jawaharlal Nehru', 'correct' => false]]],
        ];
    }

    // ==================== BANKING QUESTIONS ====================

    private function bankPercentageQuestions(): array
    {
        return [
            ['question' => 'What is 25% of 200?', 'difficulty' => 'easy', 'explanation' => '25% of 200 = (25/100) × 200 = 50.', 'options' => [['text' => '50', 'correct' => true], ['text' => '40', 'correct' => false], ['text' => '60', 'correct' => false], ['text' => '25', 'correct' => false]]],
            ['question' => 'If the price increases from ₹80 to ₹100, what is the percentage increase?', 'difficulty' => 'easy', 'explanation' => 'Increase = 20. % increase = (20/80) × 100 = 25%.', 'options' => [['text' => '25%', 'correct' => true], ['text' => '20%', 'correct' => false], ['text' => '30%', 'correct' => false], ['text' => '15%', 'correct' => false]]],
            ['question' => 'A salary is reduced by 20%. By what percent must it be increased to restore the original?', 'difficulty' => 'medium', 'explanation' => 'If reduced by 20%, new = 80. To get back to 100, increase = 20/80 × 100 = 25%.', 'options' => [['text' => '25%', 'correct' => true], ['text' => '20%', 'correct' => false], ['text' => '30%', 'correct' => false], ['text' => '15%', 'correct' => false]]],
            ['question' => 'If A is 50% more than B, by what percent is B less than A?', 'difficulty' => 'medium', 'explanation' => 'A = 1.5B. B is less than A by (0.5A)/A × 100 = 33.33%.', 'options' => [['text' => '33.33%', 'correct' => true], ['text' => '25%', 'correct' => false], ['text' => '50%', 'correct' => false], ['text' => '40%', 'correct' => false]]],
            ['question' => 'A shopkeeper gives 10% discount on MRP of ₹500. What is the selling price?', 'difficulty' => 'easy', 'explanation' => 'Discount = 50. SP = 500 - 50 = ₹450.', 'options' => [['text' => '₹450', 'correct' => true], ['text' => '₹460', 'correct' => false], ['text' => '₹440', 'correct' => false], ['text' => '₹480', 'correct' => false]]],
        ];
    }

    private function bankProfitLossQuestions(): array
    {
        return [
            ['question' => 'CP = ₹400, SP = ₹500. Find profit and profit%.', 'difficulty' => 'easy', 'explanation' => 'Profit = 100. Profit% = (100/400) × 100 = 25%.', 'options' => [['text' => '₹100, 25%', 'correct' => true], ['text' => '₹100, 20%', 'correct' => false], ['text' => '₹80, 25%', 'correct' => false], ['text' => '₹120, 30%', 'correct' => false]]],
            ['question' => 'An article bought for ₹600 is sold at 15% loss. Find the selling price.', 'difficulty' => 'easy', 'explanation' => 'Loss = 15% of 600 = 90. SP = 600 - 90 = ₹510.', 'options' => [['text' => '₹510', 'correct' => true], ['text' => '₹520', 'correct' => false], ['text' => '₹490', 'correct' => false], ['text' => '₹540', 'correct' => false]]],
            ['question' => 'If SP = ₹720 and profit = 20%, find CP.', 'difficulty' => 'medium', 'explanation' => 'CP = SP × 100/120 = 720 × 100/120 = ₹600.', 'options' => [['text' => '₹600', 'correct' => true], ['text' => '₹650', 'correct' => false], ['text' => '₹550', 'correct' => false], ['text' => '₹700', 'correct' => false]]],
            ['question' => 'Two articles are bought for ₹1000 each. One is sold at 20% profit and other at 20% loss. Find overall profit or loss.', 'difficulty' => 'hard', 'explanation' => 'SP1 = 1200, SP2 = 800. Total SP = 2000 = Total CP. No profit no loss.', 'options' => [['text' => 'No profit no loss', 'correct' => true], ['text' => '₹40 profit', 'correct' => false], ['text' => '₹40 loss', 'correct' => false], ['text' => '₹80 loss', 'correct' => false]]],
        ];
    }

    private function bankSIQuestions(): array
    {
        return [
            ['question' => 'Find SI on ₹1000 at 10% for 2 years.', 'difficulty' => 'easy', 'explanation' => 'SI = PRT/100 = 1000 × 10 × 2/100 = ₹200.', 'options' => [['text' => '₹200', 'correct' => true], ['text' => '₹100', 'correct' => false], ['text' => '₹250', 'correct' => false], ['text' => '₹150', 'correct' => false]]],
            ['question' => 'At what rate of SI will ₹500 amount to ₹600 in 4 years?', 'difficulty' => 'medium', 'explanation' => 'SI = 100. Rate = (100 × 100)/(500 × 4) = 5%.', 'options' => [['text' => '5%', 'correct' => true], ['text' => '4%', 'correct' => false], ['text' => '6%', 'correct' => false], ['text' => '8%', 'correct' => false]]],
            ['question' => 'Find the time in which ₹2000 will amount to ₹2400 at 8% SI.', 'difficulty' => 'medium', 'explanation' => 'SI = 400. Time = (400 × 100)/(2000 × 8) = 2.5 years.', 'options' => [['text' => '2.5 years', 'correct' => true], ['text' => '2 years', 'correct' => false], ['text' => '3 years', 'correct' => false], ['text' => '1.5 years', 'correct' => false]]],
        ];
    }

    private function bankPuzzleQuestions(): array
    {
        return [
            ['question' => '5 houses are in a row. A is to the left of B. C is between A and B. D is to the right of B. E is to the right of D. Who is in the middle?', 'difficulty' => 'medium', 'explanation' => 'Order: A, C, B, D, E. C is in the middle.', 'options' => [['text' => 'C', 'correct' => true], ['text' => 'B', 'correct' => false], ['text' => 'D', 'correct' => false], ['text' => 'A', 'correct' => false]]],
            ['question' => 'In a family of 6, A is the father of B, C is the mother of D, E is the sister of B, F is the brother of D. How is F related to A?', 'difficulty' => 'medium', 'explanation' => 'F is D\'s brother. D is C\'s child. If C is A\'s wife, F is A\'s son.', 'options' => [['text' => 'Grandson', 'correct' => true], ['text' => 'Son', 'correct' => false], ['text' => 'Nephew', 'correct' => false], ['text' => 'Brother', 'correct' => false]]],
            ['question' => 'If all roses are flowers and some flowers are red, which is definitely true?', 'difficulty' => 'easy', 'explanation' => 'Some roses may be red, but we cannot conclude all roses are red.', 'options' => [['text' => 'Some roses may be red', 'correct' => true], ['text' => 'All roses are red', 'correct' => false], ['text' => 'No rose is red', 'correct' => false], ['text' => 'All flowers are roses', 'correct' => false]]],
        ];
    }

    private function bankSyllogismQuestions(): array
    {
        return [
            ['question' => 'Statements: All dogs are cats. All cats are birds. Conclusions: I. All dogs are birds. II. All birds are dogs.', 'difficulty' => 'medium', 'explanation' => 'From the statements, All dogs → cats → birds. So conclusion I follows. Conclusion II does not follow.', 'options' => [['text' => 'Only I follows', 'correct' => true], ['text' => 'Only II follows', 'correct' => false], ['text' => 'Both follow', 'correct' => false], ['text' => 'Neither follows', 'correct' => false]]],
            ['question' => 'Statements: Some books are pens. All pens are chairs. Conclusions: I. Some books are chairs. II. All chairs are books.', 'difficulty' => 'medium', 'explanation' => 'Some books → pens → chairs. So some books are chairs. Conclusion I follows. II does not.', 'options' => [['text' => 'Only I follows', 'correct' => true], ['text' => 'Only II follows', 'correct' => false], ['text' => 'Both follow', 'correct' => false], ['text' => 'Neither follows', 'correct' => false]]],
        ];
    }

    private function bankClozeQuestions(): array
    {
        return [
            ['question' => 'The manager was very _____ about the new project and wanted to start immediately.', 'difficulty' => 'easy', 'explanation' => 'Enthusiastic means showing intense enjoyment or interest.', 'options' => [['text' => 'enthusiastic', 'correct' => true], ['text' => 'reluctant', 'correct' => false], ['text' => 'indifferent', 'correct' => false], ['text' => 'confused', 'correct' => false]]],
            ['question' => 'She decided to _____ her studies abroad after completing her graduation.', 'difficulty' => 'easy', 'explanation' => 'Pursue means to follow or continue an academic course.', 'options' => [['text' => 'pursue', 'correct' => true], ['text' => 'abandon', 'correct' => false], ['text' => 'postpone', 'correct' => false], ['text' => 'forget', 'correct' => false]]],
            ['question' => 'The government has taken several _____ to improve the economy.', 'difficulty' => 'medium', 'explanation' => 'Measures means actions taken to achieve a result.', 'options' => [['text' => 'measures', 'correct' => true], ['text' => 'methods', 'correct' => false], ['text' => 'ways', 'correct' => false], ['text' => 'paths', 'correct' => false]]],
        ];
    }

    private function bankBankingQuestions(): array
    {
        return [
            ['question' => 'What does CRR stand for in banking?', 'difficulty' => 'easy', 'explanation' => 'CRR = Cash Reserve Ratio. It is the percentage of deposits banks must keep with RBI.', 'options' => [['text' => 'Cash Reserve Ratio', 'correct' => true], ['text' => 'Credit Reserve Ratio', 'correct' => false], ['text' => 'Central Reserve Ratio', 'correct' => false], ['text' => 'Cash Risk Ratio', 'correct' => false]]],
            ['question' => 'What is the full form of NEFT?', 'difficulty' => 'easy', 'explanation' => 'NEFT = National Electronic Funds Transfer.', 'options' => [['text' => 'National Electronic Funds Transfer', 'correct' => true], ['text' => 'New Electronic Funds Transfer', 'correct' => false], ['text' => 'National Exchange Fund Transfer', 'correct' => false], ['text' => 'Net Electronic Fund Transfer', 'correct' => false]]],
            ['question' => 'Which bank is the banker\'s bank in India?', 'difficulty' => 'easy', 'explanation' => 'Reserve Bank of India (RBI) is the banker\'s bank.', 'options' => [['text' => 'Reserve Bank of India', 'correct' => true], ['text' => 'State Bank of India', 'correct' => false], ['text' => 'Bank of Baroda', 'correct' => false], ['text' => 'Punjab National Bank', 'correct' => false]]],
            ['question' => 'What is SLR in banking?', 'difficulty' => 'medium', 'explanation' => 'SLR = Statutory Liquidity Ratio. Banks must maintain this ratio in liquid assets.', 'options' => [['text' => 'Statutory Liquidity Ratio', 'correct' => true], ['text' => 'Standard Liquid Ratio', 'correct' => false], ['text' => 'Short-term Liquidity Ratio', 'correct' => false], ['text' => 'Safe Liquid Reserve', 'correct' => false]]],
        ];
    }

    private function bankEconomyQuestions(): array
    {
        return [
            ['question' => 'What is GDP?', 'difficulty' => 'easy', 'explanation' => 'GDP = Gross Domestic Product. Total value of goods and services produced in a country.', 'options' => [['text' => 'Gross Domestic Product', 'correct' => true], ['text' => 'General Domestic Product', 'correct' => false], ['text' => 'Gross Development Product', 'correct' => false], ['text' => 'Global Domestic Product', 'correct' => false]]],
            ['question' => 'Who controls monetary policy in India?', 'difficulty' => 'easy', 'explanation' => 'RBI (Reserve Bank of India) controls monetary policy.', 'options' => [['text' => 'RBI', 'correct' => true], ['text' => 'SEBI', 'correct' => false], ['text' => 'Finance Ministry', 'correct' => false], ['text' => 'NITI Aayog', 'correct' => false]]],
            ['question' => 'What is inflation?', 'difficulty' => 'easy', 'explanation' => 'Inflation is the general increase in prices of goods and services over time.', 'options' => [['text' => 'Rise in general price level', 'correct' => true], ['text' => 'Fall in prices', 'correct' => false], ['text' => 'Increase in production', 'correct' => false], ['text' => 'Decrease in money supply', 'correct' => false]]],
        ];
    }

    // ==================== NDA QUESTIONS ====================

    private function ndaCalculusQuestions(): array
    {
        return [
            ['question' => 'What is the derivative of x³?', 'difficulty' => 'easy', 'explanation' => 'd/dx(x³) = 3x².', 'options' => [['text' => '3x²', 'correct' => true], ['text' => 'x²', 'correct' => false], ['text' => '3x', 'correct' => false], ['text' => 'x⁴/4', 'correct' => false]]],
            ['question' => 'Find ∫2x dx.', 'difficulty' => 'easy', 'explanation' => '∫2x dx = x² + C.', 'options' => [['text' => 'x² + C', 'correct' => true], ['text' => '2x² + C', 'correct' => false], ['text' => 'x + C', 'correct' => false], ['text' => '2x + C', 'correct' => false]]],
            ['question' => 'What is the derivative of sin x?', 'difficulty' => 'easy', 'explanation' => 'd/dx(sin x) = cos x.', 'options' => [['text' => 'cos x', 'correct' => true], ['text' => '-cos x', 'correct' => false], ['text' => 'sin x', 'correct' => false], ['text' => '-sin x', 'correct' => false]]],
            ['question' => 'Find the area under y = x from x = 0 to x = 2.', 'difficulty' => 'medium', 'explanation' => 'Area = ∫₀² x dx = [x²/2]₀² = 2 - 0 = 2.', 'options' => [['text' => '2', 'correct' => true], ['text' => '4', 'correct' => false], ['text' => '1', 'correct' => false], ['text' => '3', 'correct' => false]]],
        ];
    }

    private function ndaAlgebraQuestions(): array
    {
        return [
            ['question' => 'If A = {1, 2, 3} and B = {2, 3, 4}, find A ∩ B.', 'difficulty' => 'easy', 'explanation' => 'A ∩ B = common elements = {2, 3}.', 'options' => [['text' => '{2, 3}', 'correct' => true], ['text' => '{1, 2, 3, 4}', 'correct' => false], ['text' => '{1, 4}', 'correct' => false], ['text' => '{2, 3, 4}', 'correct' => false]]],
            ['question' => 'What is the value of ⁵√32?', 'difficulty' => 'easy', 'explanation' => '⁵√32 = ⁵√(2⁵) = 2.', 'options' => [['text' => '2', 'correct' => true], ['text' => '4', 'correct' => false], ['text' => '8', 'correct' => false], ['text' => '16', 'correct' => false]]],
            ['question' => 'If log₂ 8 = x, find x.', 'difficulty' => 'easy', 'explanation' => '2ˣ = 8 = 2³. So x = 3.', 'options' => [['text' => '3', 'correct' => true], ['text' => '2', 'correct' => false], ['text' => '4', 'correct' => false], ['text' => '1', 'correct' => false]]],
            ['question' => 'Find the sum of first 10 natural numbers.', 'difficulty' => 'easy', 'explanation' => 'Sum = n(n+1)/2 = 10 × 11/2 = 55.', 'options' => [['text' => '55', 'correct' => true], ['text' => '50', 'correct' => false], ['text' => '45', 'correct' => false], ['text' => '60', 'correct' => false]]],
        ];
    }

    private function ndaPhysicsQuestions(): array
    {
        return [
            ['question' => 'What is the SI unit of force?', 'difficulty' => 'easy', 'explanation' => 'Force is measured in Newtons (N).', 'options' => [['text' => 'Newton', 'correct' => true], ['text' => 'Joule', 'correct' => false], ['text' => 'Watt', 'correct' => false], ['text' => 'Pascal', 'correct' => false]]],
            ['question' => 'What is the acceleration due to gravity on Earth?', 'difficulty' => 'easy', 'explanation' => 'g ≈ 9.8 m/s².', 'options' => [['text' => '9.8 m/s²', 'correct' => true], ['text' => '10 m/s²', 'correct' => false], ['text' => '8.9 m/s²', 'correct' => false], ['text' => '11 m/s²', 'correct' => false]]],
            ['question' => 'What is the speed of light?', 'difficulty' => 'easy', 'explanation' => 'Speed of light ≈ 3 × 10⁸ m/s.', 'options' => [['text' => '3 × 10⁸ m/s', 'correct' => true], ['text' => '3 × 10⁶ m/s', 'correct' => false], ['text' => '3 × 10¹⁰ m/s', 'correct' => false], ['text' => '3 × 10⁵ m/s', 'correct' => false]]],
            ['question' => 'What is Newton\'s second law of motion?', 'difficulty' => 'easy', 'explanation' => 'F = ma (Force equals mass times acceleration).', 'options' => [['text' => 'F = ma', 'correct' => true], ['text' => 'F = mv', 'correct' => false], ['text' => 'F = m/a', 'correct' => false], ['text' => 'F = ma²', 'correct' => false]]],
        ];
    }

    private function ndaChemistryQuestions(): array
    {
        return [
            ['question' => 'What is the atomic number of Carbon?', 'difficulty' => 'easy', 'explanation' => 'Carbon has atomic number 6.', 'options' => [['text' => '6', 'correct' => true], ['text' => '8', 'correct' => false], ['text' => '12', 'correct' => false], ['text' => '4', 'correct' => false]]],
            ['question' => 'What is the chemical symbol for Gold?', 'difficulty' => 'easy', 'explanation' => 'Gold is Au (from Latin "Aurum").', 'options' => [['text' => 'Au', 'correct' => true], ['text' => 'Ag', 'correct' => false], ['text' => 'Go', 'correct' => false], ['text' => 'Gd', 'correct' => false]]],
            ['question' => 'What is the pH of pure water?', 'difficulty' => 'easy', 'explanation' => 'Pure water has pH = 7 (neutral).', 'options' => [['text' => '7', 'correct' => true], ['text' => '0', 'correct' => false], ['text' => '14', 'correct' => false], ['text' => '1', 'correct' => false]]],
        ];
    }

    private function ndaHistoryQuestions(): array
    {
        return [
            ['question' => 'In which year did India gain independence?', 'difficulty' => 'easy', 'explanation' => 'India gained independence on 15th August 1947.', 'options' => [['text' => '1947', 'correct' => true], ['text' => '1945', 'correct' => false], ['text' => '1950', 'correct' => false], ['text' => '1942', 'correct' => false]]],
            ['question' => 'Who was the first Prime Minister of India?', 'difficulty' => 'easy', 'explanation' => 'Jawaharlal Nehru was the first PM of India.', 'options' => [['text' => 'Jawaharlal Nehru', 'correct' => true], ['text' => 'Mahatma Gandhi', 'correct' => false], ['text' => 'Sardar Patel', 'correct' => false], ['text' => 'Rajendra Prasad', 'correct' => false]]],
            ['question' => 'The Battle of Plassey was fought in which year?', 'difficulty' => 'medium', 'explanation' => 'Battle of Plassey was fought in 1757.', 'options' => [['text' => '1757', 'correct' => true], ['text' => '1764', 'correct' => false], ['text' => '1857', 'correct' => false], ['text' => '1947', 'correct' => false]]],
        ];
    }

    private function ndaGrammarQuestions(): array
    {
        return [
            ['question' => 'Choose the correct voice: "The book was read by her."', 'difficulty' => 'easy', 'explanation' => 'This is passive voice. Active: She read the book.', 'options' => [['text' => 'Passive voice', 'correct' => true], ['text' => 'Active voice', 'correct' => false], ['text' => 'Direct speech', 'correct' => false], ['text' => 'Indirect speech', 'correct' => false]]],
            ['question' => 'Identify the tense: "She has been working since morning."', 'difficulty' => 'medium', 'explanation' => 'Present perfect continuous tense.', 'options' => [['text' => 'Present perfect continuous', 'correct' => true], ['text' => 'Past perfect continuous', 'correct' => false], ['text' => 'Present continuous', 'correct' => false], ['text' => 'Future perfect', 'correct' => false]]],
        ];
    }

    // ==================== RAILWAY QUESTIONS ====================

    private function railNumberQuestions(): array
    {
        return [
            ['question' => 'What is the HCF of 24 and 36?', 'difficulty' => 'easy', 'explanation' => 'Factors of 24: 1,2,3,4,6,8,12,24. Factors of 36: 1,2,3,4,6,9,12,18,36. HCF = 12.', 'options' => [['text' => '12', 'correct' => true], ['text' => '6', 'correct' => false], ['text' => '8', 'correct' => false], ['text' => '4', 'correct' => false]]],
            ['question' => 'What is the LCM of 4, 6, and 8?', 'difficulty' => 'easy', 'explanation' => 'LCM(4,6,8) = 24.', 'options' => [['text' => '24', 'correct' => true], ['text' => '12', 'correct' => false], ['text' => '48', 'correct' => false], ['text' => '36', 'correct' => false]]],
            ['question' => 'Is 29 a prime number?', 'difficulty' => 'easy', 'explanation' => '29 is only divisible by 1 and 29. So it is prime.', 'options' => [['text' => 'Yes', 'correct' => true], ['text' => 'No', 'correct' => false], ['text' => 'Depends', 'correct' => false], ['text' => 'Cannot determine', 'correct' => false]]],
            ['question' => 'What is the remainder when 17 is divided by 5?', 'difficulty' => 'easy', 'explanation' => '17 = 5 × 3 + 2. Remainder = 2.', 'options' => [['text' => '2', 'correct' => true], ['text' => '3', 'correct' => false], ['text' => '1', 'correct' => false], ['text' => '4', 'correct' => false]]],
        ];
    }

    private function railRatioQuestions(): array
    {
        return [
            ['question' => 'If A:B = 2:3 and B:C = 4:5, find A:B:C.', 'difficulty' => 'medium', 'explanation' => 'A:B = 8:12, B:C = 12:15. So A:B:C = 8:12:15.', 'options' => [['text' => '8:12:15', 'correct' => true], ['text' => '2:3:5', 'correct' => false], ['text' => '4:6:5', 'correct' => false], ['text' => '8:12:10', 'correct' => false]]],
            ['question' => 'Divide ₹1200 in the ratio 3:5.', 'difficulty' => 'easy', 'explanation' => 'Parts = 3+5 = 8. First = 1200×3/8 = 450. Second = 1200×5/8 = 750.', 'options' => [['text' => '₹450, ₹750', 'correct' => true], ['text' => '₹600, ₹600', 'correct' => false], ['text' => '₹400, ₹800', 'correct' => false], ['text' => '₹500, ₹700', 'correct' => false]]],
            ['question' => 'The ratio of ages of A and B is 5:3. If A is 20 years old, find B\'s age.', 'difficulty' => 'easy', 'explanation' => '5/3 = 20/B. B = 20×3/5 = 12 years.', 'options' => [['text' => '12 years', 'correct' => true], ['text' => '15 years', 'correct' => false], ['text' => '10 years', 'correct' => false], ['text' => '18 years', 'correct' => false]]],
        ];
    }

    private function railAnalogyQuestions(): array
    {
        return [
            ['question' => 'Doctor : Hospital :: Teacher : ?', 'difficulty' => 'easy', 'explanation' => 'Doctor works in Hospital. Teacher works in School.', 'options' => [['text' => 'School', 'correct' => true], ['text' => 'Office', 'correct' => false], ['text' => 'Court', 'correct' => false], ['text' => 'Factory', 'correct' => false]]],
            ['question' => 'Pen : Write :: Knife : ?', 'difficulty' => 'easy', 'explanation' => 'Pen is used to write. Knife is used to cut.', 'options' => [['text' => 'Cut', 'correct' => true], ['text' => 'Sharpen', 'correct' => false], ['text' => 'Peel', 'correct' => false], ['text' => 'Slice', 'correct' => false]]],
            ['question' => 'Eye : See :: Ear : ?', 'difficulty' => 'easy', 'explanation' => 'Eye is used to see. Ear is used to hear.', 'options' => [['text' => 'Hear', 'correct' => true], ['text' => 'Listen', 'correct' => false], ['text' => 'Smell', 'correct' => false], ['text' => 'Touch', 'correct' => false]]],
        ];
    }

    private function railCurrentQuestions(): array
    {
        return [
            ['question' => 'Which Indian state recently became the first to achieve 100% vaccination? (recent context)', 'difficulty' => 'easy', 'explanation' => 'Sikkim was among the first states to achieve high vaccination coverage.', 'options' => [['text' => 'Sikkim', 'correct' => true], ['text' => 'Kerala', 'correct' => false], ['text' => 'Goa', 'correct' => false], ['text' => 'Himachal Pradesh', 'correct' => false]]],
            ['question' => 'What is the full form of ISRO?', 'difficulty' => 'easy', 'explanation' => 'ISRO = Indian Space Research Organisation.', 'options' => [['text' => 'Indian Space Research Organisation', 'correct' => true], ['text' => 'Indian Scientific Research Organisation', 'correct' => false], ['text' => 'International Space Research Organisation', 'correct' => false], ['text' => 'Indian Satellite Research Organisation', 'correct' => false]]],
        ];
    }

    private function railGeographyQuestions(): array
    {
        return [
            ['question' => 'Which is the longest river in India?', 'difficulty' => 'easy', 'explanation' => 'Ganga is the longest river in India (approx 2525 km).', 'options' => [['text' => 'Ganga', 'correct' => true], ['text' => 'Yamuna', 'correct' => false], ['text' => 'Brahmaputra', 'correct' => false], ['text' => 'Godavari', 'correct' => false]]],
            ['question' => 'Which is the highest peak in India?', 'difficulty' => 'easy', 'explanation' => 'Kanchenjunga (8,586 m) is the highest peak in India.', 'options' => [['text' => 'Kanchenjunga', 'correct' => true], ['text' => 'K2', 'correct' => false], ['text' => 'Nanda Devi', 'correct' => false], ['text' => 'Kamet', 'correct' => false]]],
            ['question' => 'How many states are there in India?', 'difficulty' => 'easy', 'explanation' => 'India has 28 states.', 'options' => [['text' => '28', 'correct' => true], ['text' => '30', 'correct' => false], ['text' => '29', 'correct' => false], ['text' => '27', 'correct' => false]]],
        ];
    }

    // ==================== POLICE QUESTIONS ====================

    private function polCodingQuestions(): array
    {
        return [
            ['question' => 'If COMPUTER is coded as DNPQVUFS, how is SCIENCE coded?', 'difficulty' => 'medium', 'explanation' => 'Each letter shifts by +1. SCIENCE → TDJFODF.', 'options' => [['text' => 'TDJFODF', 'correct' => true], ['text' => 'TDJFODF', 'correct' => false], ['text' => 'SCJFODF', 'correct' => false], ['text' => 'TDJFODE', 'correct' => false]]],
            ['question' => 'In a certain code, DELHI is written as CDKGH. How is MUMBAI written?', 'difficulty' => 'medium', 'explanation' => 'Each letter shifts by -1. MUMBAI → LTLAZH.', 'options' => [['text' => 'LTLAZH', 'correct' => true], ['text' => 'LTLBZI', 'correct' => false], ['text' => 'MVMCBJ', 'correct' => false], ['text' => 'LTKAZH', 'correct' => false]]],
            ['question' => 'If A=1, B=2, ..., what is the sum of values of L-O-V-E?', 'difficulty' => 'easy', 'explanation' => 'L=12, O=15, V=22, E=5. Sum = 54.', 'options' => [['text' => '54', 'correct' => true], ['text' => '50', 'correct' => false], ['text' => '56', 'correct' => false], ['text' => '48', 'correct' => false]]],
        ];
    }

    private function polBloodQuestions(): array
    {
        return [
            ['question' => 'A says "She is the daughter of my mother\'s only son." Who is the girl to A?', 'difficulty' => 'medium', 'explanation' => 'My mother\'s only son = A himself. So the girl is A\'s daughter.', 'options' => [['text' => 'Daughter', 'correct' => true], ['text' => 'Sister', 'correct' => false], ['text' => 'Niece', 'correct' => false], ['text' => 'Mother', 'correct' => false]]],
            ['question' => 'Pointing to a man, a woman says "He is the father of my only daughter." How is the man related to the woman?', 'difficulty' => 'easy', 'explanation' => 'The man is the father of the woman\'s daughter, so he is the woman\'s husband.', 'options' => [['text' => 'Husband', 'correct' => true], ['text' => 'Father', 'correct' => false], ['text' => 'Brother', 'correct' => false], ['text' => 'Son', 'correct' => false]]],
        ];
    }

    private function polIPCQuestions(): array
    {
        return [
            ['question' => 'What does IPC stand for?', 'difficulty' => 'easy', 'explanation' => 'IPC = Indian Penal Code.', 'options' => [['text' => 'Indian Penal Code', 'correct' => true], ['text' => 'Indian Police Code', 'correct' => false], ['text' => 'International Penal Code', 'correct' => false], ['text' => 'Indian Punishment Code', 'correct' => false]]],
            ['question' => 'Which section of IPC deals with murder?', 'difficulty' => 'medium', 'explanation' => 'Section 302 of IPC deals with murder.', 'options' => [['text' => 'Section 302', 'correct' => true], ['text' => 'Section 304', 'correct' => false], ['text' => 'Section 376', 'correct' => false], ['text' => 'Section 420', 'correct' => false]]],
            ['question' => 'What is the minimum age for criminal responsibility under IPC?', 'difficulty' => 'medium', 'explanation' => 'Under IPC, a child below 7 years is not liable for any offence.', 'options' => [['text' => '7 years', 'correct' => true], ['text' => '10 years', 'correct' => false], ['text' => '12 years', 'correct' => false], ['text' => '14 years', 'correct' => false]]],
        ];
    }

    private function polConstitutionQuestions(): array
    {
        return [
            ['question' => 'How many articles are there in the Indian Constitution?', 'difficulty' => 'medium', 'explanation' => 'Originally 395 articles in 22 parts. Now around 448 articles.', 'options' => [['text' => '395', 'correct' => true], ['text' => '400', 'correct' => false], ['text' => '350', 'correct' => false], ['text' => '450', 'correct' => false]]],
            ['question' => 'Which article of the Indian Constitution deals with Right to Equality?', 'difficulty' => 'easy', 'explanation' => 'Articles 14-18 deal with Right to Equality.', 'options' => [['text' => 'Article 14', 'correct' => true], ['text' => 'Article 19', 'correct' => false], ['text' => 'Article 21', 'correct' => false], ['text' => 'Article 32', 'correct' => false]]],
            ['question' => 'Who is the head of the Indian state?', 'difficulty' => 'easy', 'explanation' => 'The President of India is the head of state.', 'options' => [['text' => 'President', 'correct' => true], ['text' => 'Prime Minister', 'correct' => false], ['text' => 'Chief Justice', 'correct' => false], ['text' => 'Governor', 'correct' => false]]],
        ];
    }

    private function polArithmeticQuestions(): array
    {
        return [
            ['question' => 'A car travels 150 km in 3 hours. What is its speed?', 'difficulty' => 'easy', 'explanation' => 'Speed = 150/3 = 50 km/h.', 'options' => [['text' => '50 km/h', 'correct' => true], ['text' => '60 km/h', 'correct' => false], ['text' => '40 km/h', 'correct' => false], ['text' => '55 km/h', 'correct' => false]]],
            ['question' => 'What is 20% of 250?', 'difficulty' => 'easy', 'explanation' => '20% of 250 = (20/100) × 250 = 50.', 'options' => [['text' => '50', 'correct' => true], ['text' => '40', 'correct' => false], ['text' => '60', 'correct' => false], ['text' => '45', 'correct' => false]]],
            ['question' => 'If 5 notebooks cost ₹100, what is the cost of 8 notebooks?', 'difficulty' => 'easy', 'explanation' => 'Cost per notebook = 100/5 = 20. Cost of 8 = 8 × 20 = ₹160.', 'options' => [['text' => '₹160', 'correct' => true], ['text' => '₹150', 'correct' => false], ['text' => '₹180', 'correct' => false], ['text' => '₹120', 'correct' => false]]],
        ];
    }

    // ==================== STATE PSC QUESTIONS ====================

    private function pscHistoryQuestions(): array
    {
        return [
            ['question' => 'Who was the founder of the Maurya Empire?', 'difficulty' => 'easy', 'explanation' => 'Chandragupta Maurya founded the Maurya Empire.', 'options' => [['text' => 'Chandragupta Maurya', 'correct' => true], ['text' => 'Ashoka', 'correct' => false], ['text' => 'Bindusara', 'correct' => false], ['text' => 'Harsha', 'correct' => false]]],
            ['question' => 'The Quit India Movement was launched in which year?', 'difficulty' => 'easy', 'explanation' => 'Quit India Movement was launched in 1942.', 'options' => [['text' => '1942', 'correct' => true], ['text' => '1940', 'correct' => false], ['text' => '1944', 'correct' => false], ['text' => '1946', 'correct' => false]]],
            ['question' => 'Who wrote "Arthashastra"?', 'difficulty' => 'easy', 'explanation' => 'Kautilya (Chanakya) wrote Arthashastra.', 'options' => [['text' => 'Kautilya', 'correct' => true], ['text' => 'Manu', 'correct' => false], ['text' => 'Valmiki', 'correct' => false], ['text' => 'Ved Vyasa', 'correct' => false]]],
            ['question' => 'The Revolt of 1857 started from which place?', 'difficulty' => 'easy', 'explanation' => 'The revolt started from Meerut on 10th May 1857.', 'options' => [['text' => 'Meerut', 'correct' => true], ['text' => 'Delhi', 'correct' => false], ['text' => 'Kanpur', 'correct' => false], ['text' => 'Lucknow', 'correct' => false]]],
        ];
    }

    private function pscPolityQuestions(): array
    {
        return [
            ['question' => 'How many fundamental rights are there in the Indian Constitution?', 'difficulty' => 'easy', 'explanation' => 'Originally 7 fundamental rights, now 6 (Right to Property was removed).', 'options' => [['text' => '6', 'correct' => true], ['text' => '7', 'correct' => false], ['text' => '5', 'correct' => false], ['text' => '8', 'correct' => false]]],
            ['question' => 'Which article is known as the "Heart and Soul" of the Constitution?', 'difficulty' => 'medium', 'explanation' => 'Article 32 (Right to Constitutional Remedies) is called the heart and soul.', 'options' => [['text' => 'Article 32', 'correct' => true], ['text' => 'Article 14', 'correct' => false], ['text' => 'Article 19', 'correct' => false], ['text' => 'Article 21', 'correct' => false]]],
            ['question' => 'Who appoints the Chief Justice of India?', 'difficulty' => 'easy', 'explanation' => 'The President of India appoints the CJI.', 'options' => [['text' => 'President', 'correct' => true], ['text' => 'Prime Minister', 'correct' => false], ['text' => 'Parliament', 'correct' => false], ['text' => 'Supreme Court', 'correct' => false]]],
        ];
    }

    private function pscGeographyQuestions(): array
    {
        return [
            ['question' => 'Which is the smallest state in India by area?', 'difficulty' => 'easy', 'explanation' => 'Goa is the smallest state by area (3,702 km²).', 'options' => [['text' => 'Goa', 'correct' => true], ['text' => 'Sikkim', 'correct' => false], ['text' => 'Tripura', 'correct' => false], ['text' => 'Mizoram', 'correct' => false]]],
            ['question' => 'Which river is known as the "Sorrow of Bengal"?', 'difficulty' => 'medium', 'explanation' => 'Damodar River is called the Sorrow of Bengal.', 'options' => [['text' => 'Damodar', 'correct' => true], ['text' => 'Kosi', 'correct' => false], ['text' => 'Brahmaputra', 'correct' => false], ['text' => 'Mahanadi', 'correct' => false]]],
            ['question' => 'The Tropic of Cancer passes through how many Indian states?', 'difficulty' => 'medium', 'explanation' => 'Tropic of Cancer passes through 8 states: Gujarat, Rajasthan, MP, Chhattisgarh, Jharkhand, WB, Tripura, Mizoram.', 'options' => [['text' => '8', 'correct' => true], ['text' => '7', 'correct' => false], ['text' => '9', 'correct' => false], ['text' => '6', 'correct' => false]]],
        ];
    }

    private function pscAptitudeQuestions(): array
    {
        return [
            ['question' => 'If the sum of three consecutive odd numbers is 45, find the largest number.', 'difficulty' => 'medium', 'explanation' => 'Let numbers be x, x+2, x+4. 3x+6=45. 3x=39. x=13. Numbers: 13, 15, 17. Largest = 17.', 'options' => [['text' => '17', 'correct' => true], ['text' => '15', 'correct' => false], ['text' => '19', 'correct' => false], ['text' => '13', 'correct' => false]]],
            ['question' => 'What is the compound interest on ₹1000 at 10% for 2 years?', 'difficulty' => 'medium', 'explanation' => 'CI = P(1+r/100)² - P = 1000(1.1)² - 1000 = 1210 - 1000 = ₹210.', 'options' => [['text' => '₹210', 'correct' => true], ['text' => '₹200', 'correct' => false], ['text' => '₹220', 'correct' => false], ['text' => '₹190', 'correct' => false]]],
            ['question' => 'A pipe can fill a tank in 20 hours. Another pipe can empty it in 30 hours. How long to fill the tank when both are open?', 'difficulty' => 'medium', 'explanation' => 'Net rate = 1/20 - 1/30 = 1/60. Time = 60 hours.', 'options' => [['text' => '60 hours', 'correct' => true], ['text' => '50 hours', 'correct' => false], ['text' => '40 hours', 'correct' => false], ['text' => '25 hours', 'correct' => false]]],
        ];
    }

    private function pscLogicQuestions(): array
    {
        return [
            ['question' => 'If 3 + 4 = 21, 5 + 6 = 55, 2 + 7 = ?, find the pattern.', 'difficulty' => 'medium', 'explanation' => 'Pattern: a + b = a² + b² + ab. 3+4=9+16+12=37. Wait, 3²+4²=25, 25+12=37. But given 21. Let me check: 3×4+9=21. 5×6+25=55. So a×b+a²=21,55. 2×7+4=18.', 'options' => [['text' => '18', 'correct' => true], ['text' => '23', 'correct' => false], ['text' => '53', 'correct' => false], ['text' => '14', 'correct' => false]]],
            ['question' => 'Find the next number: 2, 6, 12, 20, 30, ?', 'difficulty' => 'medium', 'explanation' => 'Differences: 4, 6, 8, 10. Next difference = 12. Next number = 30 + 12 = 42.', 'options' => [['text' => '42', 'correct' => true], ['text' => '36', 'correct' => false], ['text' => '40', 'correct' => false], ['text' => '44', 'correct' => false]]],
            ['question' => 'If APPLE = 50, ORANGE = 60, what is GRAPE?', 'difficulty' => 'medium', 'explanation' => 'Sum of positions: A(1)+P(16)+P(16)+L(12)+E(5) = 50. G(7)+R(18)+A(1)+P(16)+E(5) = 47. But given pattern might be different.', 'options' => [['text' => '47', 'correct' => true], ['text' => '50', 'correct' => false], ['text' => '55', 'correct' => false], ['text' => '45', 'correct' => false]]],
        ];
    }

    private function seedMockTests(): void
    {
        $ssc = Exam::where('slug', 'ssc')->first();
        $banking = Exam::where('slug', 'banking')->first();
        $nda = Exam::where('slug', 'nda')->first();
        $railway = Exam::where('slug', 'railway')->first();
        $police = Exam::where('slug', 'police')->first();
        $psc = Exam::where('slug', 'state-psc')->first();

        $sscTier1Pattern = ExamPattern::where('slug', 'ssc-cgl-tier1')->first();
        $bankPrelimsPattern = ExamPattern::where('slug', 'ibps-po-prelims')->first();
        $ndaPattern = ExamPattern::where('slug', 'nda-written')->first();
        $railCBT1Pattern = ExamPattern::where('slug', 'rrb-cbt1')->first();
        $polPattern = ExamPattern::where('slug', 'police-constable')->first();
        $pscPrelimsPattern = ExamPattern::where('slug', 'state-psc-prelims')->first();

        // Helper: get questions for a subject
        $getQuestions = fn($examId, $subjectName, $limit) => Question::where('exam_id', $examId)
            ->where('subject_id', Subject::where('exam_id', $examId)->where('name', $subjectName)->first()->id)
            ->where('status', 'approved')
            ->inRandomOrder()
            ->limit($limit)
            ->get();

        // ==================== SSC CGL TIER 1 MOCK TEST ====================
        $sscTest = MockTest::create([
            'exam_id' => $ssc->id,
            'exam_pattern_id' => $sscTier1Pattern->id,
            'title' => 'SSC CGL Tier 1 Mock Test - 1',
            'description' => 'Full-length SSC CGL Tier 1 mock test with 4 sections: Quant, Reasoning, GK, English. 100 questions, 60 minutes, 200 marks.',
            'duration_minutes' => 60,
            'total_marks' => 200,
            'total_questions' => 100,
            'difficulty' => 'medium',
            'negative_marking' => true,
            'negative_marking_value' => 0.50,
            'status' => 'published',
        ]);

        $sscSections = $sscTier1Pattern->sections;
        $sectionSubjects = ['Mathematics', 'Reasoning', 'General Knowledge', 'English'];
        $order = 1;
        foreach ($sscSections as $section) {
            $subjectName = $sectionSubjects[$section->order - 1];
            $mts = MockTestSection::create([
                'mock_test_id' => $sscTest->id,
                'exam_section_id' => $section->id,
                'name' => $section->name,
                'total_questions' => $section->total_questions,
                'total_marks' => $section->total_marks,
                'duration_minutes' => null,
                'marks_per_question' => $section->marks_per_question,
                'negative_marks_per_question' => $section->negative_marks_per_question,
                'is_mandatory' => true,
                'order' => $section->order,
            ]);

            $questions = $getQuestions($ssc->id, $subjectName, $section->total_questions);
            foreach ($questions as $q) {
                MockTestQuestion::create([
                    'mock_test_id' => $sscTest->id,
                    'question_id' => $q->id,
                    'mock_test_section_id' => $mts->id,
                    'marks' => $section->marks_per_question,
                    'negative_marks' => $section->negative_marks_per_question,
                    'order' => $order++,
                ]);
            }
        }

        // ==================== BANKING PO PRELIMS MOCK TEST ====================
        $bankTest = MockTest::create([
            'exam_id' => $banking->id,
            'exam_pattern_id' => $bankPrelimsPattern->id,
            'title' => 'IBPS PO Prelims Mock Test - 1',
            'description' => 'Full-length IBPS PO Prelims mock test with sectional timing. Quant (35), Reasoning (35), English (30).',
            'duration_minutes' => 60,
            'total_marks' => 100,
            'total_questions' => 100,
            'difficulty' => 'medium',
            'negative_marking' => true,
            'negative_marking_value' => 0.25,
            'status' => 'published',
        ]);

        $bankSections = $bankPrelimsPattern->sections;
        $bankSubjects = ['Quantitative Aptitude', 'Reasoning', 'English Language'];
        $order = 1;
        foreach ($bankSections as $section) {
            $subjectName = $bankSubjects[$section->order - 1];
            $mts = MockTestSection::create([
                'mock_test_id' => $bankTest->id,
                'exam_section_id' => $section->id,
                'name' => $section->name,
                'total_questions' => $section->total_questions,
                'total_marks' => $section->total_marks,
                'duration_minutes' => $section->duration_minutes,
                'marks_per_question' => $section->marks_per_question,
                'negative_marks_per_question' => $section->negative_marks_per_question,
                'is_mandatory' => true,
                'order' => $section->order,
            ]);

            $questions = $getQuestions($banking->id, $subjectName, $section->total_questions);
            foreach ($questions as $q) {
                MockTestQuestion::create([
                    'mock_test_id' => $bankTest->id,
                    'question_id' => $q->id,
                    'mock_test_section_id' => $mts->id,
                    'marks' => $section->marks_per_question,
                    'negative_marks' => $section->negative_marks_per_question,
                    'order' => $order++,
                ]);
            }
        }

        // ==================== NDA MOCK TEST ====================
        $ndaTest = MockTest::create([
            'exam_id' => $nda->id,
            'exam_pattern_id' => $ndaPattern->id,
            'title' => 'NDA Written Exam Mock Test - 1',
            'description' => 'NDA & NA written exam mock test. Mathematics (120q, 300 marks) + GAT (60q, 600 marks).',
            'duration_minutes' => 150,
            'total_marks' => 900,
            'total_questions' => 180,
            'difficulty' => 'hard',
            'negative_marking' => true,
            'negative_marking_value' => 1.33,
            'status' => 'published',
        ]);

        $ndaSections = $ndaPattern->sections;
        $ndaSubjects = ['Mathematics', 'General Ability Test'];
        $order = 1;
        foreach ($ndaSections as $section) {
            $subjectName = $ndaSubjects[$section->order - 1];
            $mts = MockTestSection::create([
                'mock_test_id' => $ndaTest->id,
                'exam_section_id' => $section->id,
                'name' => $section->name,
                'total_questions' => $section->total_questions,
                'total_marks' => $section->total_marks,
                'duration_minutes' => $section->duration_minutes,
                'marks_per_question' => $section->marks_per_question,
                'negative_marks_per_question' => $section->negative_marks_per_question,
                'is_mandatory' => true,
                'order' => $section->order,
            ]);

            $questions = $getQuestions($nda->id, $subjectName, min($section->total_questions, 20));
            foreach ($questions as $q) {
                MockTestQuestion::create([
                    'mock_test_id' => $ndaTest->id,
                    'question_id' => $q->id,
                    'mock_test_section_id' => $mts->id,
                    'marks' => $section->marks_per_question,
                    'negative_marks' => $section->negative_marks_per_question,
                    'order' => $order++,
                ]);
            }
        }

        // ==================== RAILWAY RRB CBT 1 MOCK TEST ====================
        $railTest = MockTest::create([
            'exam_id' => $railway->id,
            'exam_pattern_id' => $railCBT1Pattern->id,
            'title' => 'RRB CBT Stage 1 Mock Test - 1',
            'description' => 'Railway CBT Stage 1 mock test. Math (30q), Reasoning (30q), GK (40q). 100 questions, 90 min, 100 marks.',
            'duration_minutes' => 90,
            'total_marks' => 100,
            'total_questions' => 100,
            'difficulty' => 'medium',
            'negative_marking' => true,
            'negative_marking_value' => 0.33,
            'status' => 'published',
        ]);

        $railSections = $railCBT1Pattern->sections;
        $railSubjects = ['Mathematics', 'General Intelligence', 'General Awareness'];
        $order = 1;
        foreach ($railSections as $section) {
            $subjectName = $railSubjects[$section->order - 1];
            $mts = MockTestSection::create([
                'mock_test_id' => $railTest->id,
                'exam_section_id' => $section->id,
                'name' => $section->name,
                'total_questions' => $section->total_questions,
                'total_marks' => $section->total_marks,
                'duration_minutes' => null,
                'marks_per_question' => $section->marks_per_question,
                'negative_marks_per_question' => $section->negative_marks_per_question,
                'is_mandatory' => true,
                'order' => $section->order,
            ]);

            $questions = $getQuestions($railway->id, $subjectName, $section->total_questions);
            foreach ($questions as $q) {
                MockTestQuestion::create([
                    'mock_test_id' => $railTest->id,
                    'question_id' => $q->id,
                    'mock_test_section_id' => $mts->id,
                    'marks' => $section->marks_per_question,
                    'negative_marks' => $section->negative_marks_per_question,
                    'order' => $order++,
                ]);
            }
        }

        // ==================== POLICE CONSTABLE MOCK TEST ====================
        $polTest = MockTest::create([
            'exam_id' => $police->id,
            'exam_pattern_id' => $polPattern->id,
            'title' => 'Police Constable Written Exam - 1',
            'description' => 'Police Constable written exam mock test. Reasoning (35q), GK (35q), Math (30q). 100 questions, 120 min.',
            'duration_minutes' => 120,
            'total_marks' => 100,
            'total_questions' => 100,
            'difficulty' => 'medium',
            'negative_marking' => true,
            'negative_marking_value' => 0.25,
            'status' => 'published',
        ]);

        $polSections = $polPattern->sections;
        $polSubjects = ['Reasoning', 'General Knowledge', 'Mathematics'];
        $order = 1;
        foreach ($polSections as $section) {
            $subjectName = $polSubjects[$section->order - 1];
            $mts = MockTestSection::create([
                'mock_test_id' => $polTest->id,
                'exam_section_id' => $section->id,
                'name' => $section->name,
                'total_questions' => $section->total_questions,
                'total_marks' => $section->total_marks,
                'duration_minutes' => null,
                'marks_per_question' => $section->marks_per_question,
                'negative_marks_per_question' => $section->negative_marks_per_question,
                'is_mandatory' => true,
                'order' => $section->order,
            ]);

            $questions = $getQuestions($police->id, $subjectName, $section->total_questions);
            foreach ($questions as $q) {
                MockTestQuestion::create([
                    'mock_test_id' => $polTest->id,
                    'question_id' => $q->id,
                    'mock_test_section_id' => $mts->id,
                    'marks' => $section->marks_per_question,
                    'negative_marks' => $section->negative_marks_per_question,
                    'order' => $order++,
                ]);
            }
        }

        // ==================== STATE PSC PRELIMS MOCK TEST ====================
        $pscTest = MockTest::create([
            'exam_id' => $psc->id,
            'exam_pattern_id' => $pscPrelimsPattern->id,
            'title' => 'State PSC Prelims Mock Test - 1',
            'description' => 'State PSC Prelims mock test. General Studies (100q) + Aptitude (100q). 200 questions, 180 min, 200 marks.',
            'duration_minutes' => 180,
            'total_marks' => 200,
            'total_questions' => 200,
            'difficulty' => 'hard',
            'negative_marking' => true,
            'negative_marking_value' => 0.33,
            'status' => 'published',
        ]);

        $pscSections = $pscPrelimsPattern->sections;
        $pscSubjects = ['General Studies', 'Mathematics'];
        $order = 1;
        foreach ($pscSections as $section) {
            $subjectName = $pscSubjects[$section->order - 1];
            $mts = MockTestSection::create([
                'mock_test_id' => $pscTest->id,
                'exam_section_id' => $section->id,
                'name' => $section->name,
                'total_questions' => $section->total_questions,
                'total_marks' => $section->total_marks,
                'duration_minutes' => null,
                'marks_per_question' => $section->marks_per_question,
                'negative_marks_per_question' => $section->negative_marks_per_question,
                'is_mandatory' => true,
                'order' => $section->order,
            ]);

            $questions = $getQuestions($psc->id, $subjectName, min($section->total_questions, 16));
            foreach ($questions as $q) {
                MockTestQuestion::create([
                    'mock_test_id' => $pscTest->id,
                    'question_id' => $q->id,
                    'mock_test_section_id' => $mts->id,
                    'marks' => $section->marks_per_question,
                    'negative_marks' => $section->negative_marks_per_question,
                    'order' => $order++,
                ]);
            }
        }
    }
}
