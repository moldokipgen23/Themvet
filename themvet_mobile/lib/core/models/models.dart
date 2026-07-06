class User {
  final int id;
  final String name;
  final String email;
  final String? phone;
  final int? targetExamId;
  final bool isActive;
  final List<Role>? roles;
  final Exam? targetExam;

  User({
    required this.id,
    required this.name,
    required this.email,
    this.phone,
    this.targetExamId,
    this.isActive = true,
    this.roles,
    this.targetExam,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'],
      name: json['name'],
      email: json['email'],
      phone: json['phone'],
      targetExamId: json['target_exam_id'],
      isActive: json['is_active'] ?? true,
      roles: json['roles'] != null
          ? (json['roles'] as List).map((r) => Role.fromJson(r)).toList()
          : null,
      targetExam: json['target_exam'] != null
          ? Exam.fromJson(json['target_exam'])
          : null,
    );
  }

  bool hasRole(String roleName) {
    return roles?.any((r) => r.name == roleName) ?? false;
  }

  bool get isAdmin => hasRole('admin');
  bool get isTeacher => hasRole('teacher');
  bool get isStudent => hasRole('student');
}

class Role {
  final int id;
  final String name;
  final String? description;

  Role({required this.id, required this.name, this.description});

  factory Role.fromJson(Map<String, dynamic> json) {
    return Role(
      id: json['id'],
      name: json['name'],
      description: json['description'],
    );
  }
}

class Exam {
  final int id;
  final String name;
  final String slug;
  final String? description;
  final bool isActive;
  final List<Subject>? subjects;
  final List<ExamPattern>? patterns;

  Exam({
    required this.id,
    required this.name,
    required this.slug,
    this.description,
    this.isActive = true,
    this.subjects,
    this.patterns,
  });

  factory Exam.fromJson(Map<String, dynamic> json) {
    return Exam(
      id: json['id'],
      name: json['name'],
      slug: json['slug'],
      description: json['description'],
      isActive: json['is_active'] ?? true,
      subjects: json['subjects'] != null
          ? (json['subjects'] as List).map((s) => Subject.fromJson(s)).toList()
          : null,
      patterns: json['patterns'] != null
          ? (json['patterns'] as List).map((p) => ExamPattern.fromJson(p)).toList()
          : null,
    );
  }
}

class Subject {
  final int id;
  final int examId;
  final String name;
  final String slug;
  final String? description;
  final bool isActive;
  final List<Topic>? topics;

  Subject({
    required this.id,
    required this.examId,
    required this.name,
    required this.slug,
    this.description,
    this.isActive = true,
    this.topics,
  });

  factory Subject.fromJson(Map<String, dynamic> json) {
    return Subject(
      id: json['id'],
      examId: json['exam_id'],
      name: json['name'],
      slug: json['slug'],
      description: json['description'],
      isActive: json['is_active'] ?? true,
      topics: json['topics'] != null
          ? (json['topics'] as List).map((t) => Topic.fromJson(t)).toList()
          : null,
    );
  }
}

class Topic {
  final int id;
  final int subjectId;
  final String name;
  final String slug;
  final String? description;
  final bool isActive;

  Topic({
    required this.id,
    required this.subjectId,
    required this.name,
    required this.slug,
    this.description,
    this.isActive = true,
  });

  factory Topic.fromJson(Map<String, dynamic> json) {
    return Topic(
      id: json['id'],
      subjectId: json['subject_id'],
      name: json['name'],
      slug: json['slug'],
      description: json['description'],
      isActive: json['is_active'] ?? true,
    );
  }
}

class ExamPattern {
  final int id;
  final int examId;
  final String name;
  final String slug;
  final String? description;
  final int durationMinutes;
  final int totalMarks;
  final int totalQuestions;
  final int sectionsCount;
  final bool negativeMarking;
  final double negativeMarkingValue;
  final bool isOfficial;
  final bool isActive;
  final int order;
  final Map<String, dynamic>? details;
  final List<ExamSection>? sections;

  ExamPattern({
    required this.id,
    required this.examId,
    required this.name,
    required this.slug,
    this.description,
    required this.durationMinutes,
    required this.totalMarks,
    required this.totalQuestions,
    required this.sectionsCount,
    required this.negativeMarking,
    required this.negativeMarkingValue,
    required this.isOfficial,
    this.isActive = true,
    this.order = 0,
    this.details,
    this.sections,
  });

  factory ExamPattern.fromJson(Map<String, dynamic> json) {
    return ExamPattern(
      id: json['id'],
      examId: json['exam_id'],
      name: json['name'],
      slug: json['slug'],
      description: json['description'],
      durationMinutes: json['duration_minutes'],
      totalMarks: json['total_marks'],
      totalQuestions: json['total_questions'],
      sectionsCount: json['sections_count'],
      negativeMarking: json['negative_marking'],
      negativeMarkingValue: (json['negative_marking_value'] ?? 0).toDouble(),
      isOfficial: json['is_official'] ?? false,
      isActive: json['is_active'] ?? true,
      order: json['order'] ?? 0,
      details: json['details'],
      sections: json['sections'] != null
          ? (json['sections'] as List).map((s) => ExamSection.fromJson(s)).toList()
          : null,
    );
  }
}

class ExamSection {
  final int id;
  final int examPatternId;
  final String name;
  final String slug;
  final int? subjectId;
  final int totalQuestions;
  final int totalMarks;
  final int? durationMinutes;
  final double marksPerQuestion;
  final double negativeMarksPerQuestion;
  final String? difficultyRange;
  final bool isMandatory;
  final int order;

  ExamSection({
    required this.id,
    required this.examPatternId,
    required this.name,
    required this.slug,
    this.subjectId,
    required this.totalQuestions,
    required this.totalMarks,
    this.durationMinutes,
    required this.marksPerQuestion,
    required this.negativeMarksPerQuestion,
    this.difficultyRange,
    this.isMandatory = true,
    this.order = 0,
  });

  factory ExamSection.fromJson(Map<String, dynamic> json) {
    return ExamSection(
      id: json['id'],
      examPatternId: json['exam_pattern_id'],
      name: json['name'],
      slug: json['slug'],
      subjectId: json['subject_id'],
      totalQuestions: json['total_questions'],
      totalMarks: json['total_marks'],
      durationMinutes: json['duration_minutes'],
      marksPerQuestion: (json['marks_per_question'] ?? 1).toDouble(),
      negativeMarksPerQuestion: (json['negative_marks_per_question'] ?? 0).toDouble(),
      difficultyRange: json['difficulty_range'],
      isMandatory: json['is_mandatory'] ?? true,
      order: json['order'] ?? 0,
    );
  }
}

class MockTestSection {
  final int id;
  final int mockTestId;
  final int? examSectionId;
  final String name;
  final int totalQuestions;
  final int totalMarks;
  final int? durationMinutes;
  final double marksPerQuestion;
  final double negativeMarksPerQuestion;
  final bool isMandatory;
  final int order;

  MockTestSection({
    required this.id,
    required this.mockTestId,
    this.examSectionId,
    required this.name,
    required this.totalQuestions,
    required this.totalMarks,
    this.durationMinutes,
    required this.marksPerQuestion,
    required this.negativeMarksPerQuestion,
    this.isMandatory = true,
    this.order = 0,
  });

  factory MockTestSection.fromJson(Map<String, dynamic> json) {
    return MockTestSection(
      id: json['id'],
      mockTestId: json['mock_test_id'],
      examSectionId: json['exam_section_id'],
      name: json['name'],
      totalQuestions: json['total_questions'],
      totalMarks: json['total_marks'],
      durationMinutes: json['duration_minutes'],
      marksPerQuestion: (json['marks_per_question'] ?? 1).toDouble(),
      negativeMarksPerQuestion: (json['negative_marks_per_question'] ?? 0).toDouble(),
      isMandatory: json['is_mandatory'] ?? true,
      order: json['order'] ?? 0,
    );
  }
}

class Question {
  final int id;
  final int examId;
  final int subjectId;
  final int topicId;
  final String questionText;
  final String questionType;
  final String difficulty;
  final String? explanation;
  final String status;
  final List<QuestionOption>? options;

  Question({
    required this.id,
    required this.examId,
    required this.subjectId,
    required this.topicId,
    required this.questionText,
    required this.questionType,
    required this.difficulty,
    this.explanation,
    required this.status,
    this.options,
  });

  factory Question.fromJson(Map<String, dynamic> json) {
    return Question(
      id: json['id'],
      examId: json['exam_id'],
      subjectId: json['subject_id'],
      topicId: json['topic_id'],
      questionText: json['question_text'],
      questionType: json['question_type'],
      difficulty: json['difficulty'],
      explanation: json['explanation'],
      status: json['status'],
      options: json['options'] != null
          ? (json['options'] as List)
              .map((o) => QuestionOption.fromJson(o))
              .toList()
          : null,
    );
  }
}

class QuestionOption {
  final int id;
  final int questionId;
  final String optionText;
  final bool isCorrect;
  final int order;

  QuestionOption({
    required this.id,
    required this.questionId,
    required this.optionText,
    required this.isCorrect,
    required this.order,
  });

  factory QuestionOption.fromJson(Map<String, dynamic> json) {
    return QuestionOption(
      id: json['id'],
      questionId: json['question_id'],
      optionText: json['option_text'],
      isCorrect: json['is_correct'],
      order: json['order'],
    );
  }
}

class MockTest {
  final int id;
  final int examId;
  final int? examPatternId;
  final String title;
  final String? description;
  final int durationMinutes;
  final int totalMarks;
  final int totalQuestions;
  final String difficulty;
  final bool negativeMarking;
  final double negativeMarkingValue;
  final bool isOfficial;
  final String status;
  final Exam? exam;
  final List<MockTestQuestion>? questions;
  final List<MockTestSection>? sections;

  MockTest({
    required this.id,
    required this.examId,
    this.examPatternId,
    required this.title,
    this.description,
    required this.durationMinutes,
    required this.totalMarks,
    this.totalQuestions = 0,
    this.difficulty = 'medium',
    required this.negativeMarking,
    required this.negativeMarkingValue,
    required this.isOfficial,
    required this.status,
    this.exam,
    this.questions,
    this.sections,
  });

  factory MockTest.fromJson(Map<String, dynamic> json) {
    return MockTest(
      id: json['id'],
      examId: json['exam_id'],
      examPatternId: json['exam_pattern_id'],
      title: json['title'],
      description: json['description'],
      durationMinutes: json['duration_minutes'],
      totalMarks: json['total_marks'],
      totalQuestions: json['total_questions'] ?? 0,
      difficulty: json['difficulty'] ?? 'medium',
      negativeMarking: json['negative_marking'],
      negativeMarkingValue: (json['negative_marking_value'] ?? 0).toDouble(),
      isOfficial: json['is_official'] ?? false,
      status: json['status'],
      exam: json['exam'] != null ? Exam.fromJson(json['exam']) : null,
      questions: json['questions'] != null
          ? (json['questions'] as List)
              .map((q) => MockTestQuestion.fromJson(q))
              .toList()
          : null,
      sections: json['sections'] != null
          ? (json['sections'] as List)
              .map((s) => MockTestSection.fromJson(s))
              .toList()
          : null,
    );
  }
}

class MockTestQuestion {
  final int id;
  final int mockTestId;
  final int questionId;
  final int? mockTestSectionId;
  final int marks;
  final double negativeMarks;
  final int order;
  final Question? question;
  final MockTestSection? section;

  MockTestQuestion({
    required this.id,
    required this.mockTestId,
    required this.questionId,
    this.mockTestSectionId,
    required this.marks,
    required this.negativeMarks,
    required this.order,
    this.question,
    this.section,
  });

  factory MockTestQuestion.fromJson(Map<String, dynamic> json) {
    return MockTestQuestion(
      id: json['id'],
      mockTestId: json['mock_test_id'],
      questionId: json['question_id'],
      mockTestSectionId: json['mock_test_section_id'],
      marks: json['marks'],
      negativeMarks: (json['negative_marks'] ?? 0).toDouble(),
      order: json['order'],
      question: json['question'] != null
          ? Question.fromJson(json['question'])
          : null,
      section: json['section'] != null
          ? MockTestSection.fromJson(json['section'])
          : null,
    );
  }
}

class Attempt {
  final int id;
  final int mockTestId;
  final int userId;
  final DateTime startedAt;
  final DateTime? submittedAt;
  final double score;
  final int totalMarks;
  final double accuracy;
  final int timeSpentSeconds;
  final String status;
  final MockTest? mockTest;
  final List<AttemptAnswer>? answers;

  Attempt({
    required this.id,
    required this.mockTestId,
    required this.userId,
    required this.startedAt,
    this.submittedAt,
    required this.score,
    required this.totalMarks,
    required this.accuracy,
    required this.timeSpentSeconds,
    required this.status,
    this.mockTest,
    this.answers,
  });

  factory Attempt.fromJson(Map<String, dynamic> json) {
    return Attempt(
      id: json['id'],
      mockTestId: json['mock_test_id'],
      userId: json['user_id'],
      startedAt: DateTime.parse(json['started_at']),
      submittedAt: json['submitted_at'] != null
          ? DateTime.parse(json['submitted_at'])
          : null,
      score: (json['score'] ?? 0).toDouble(),
      totalMarks: json['total_marks'],
      accuracy: (json['accuracy'] ?? 0).toDouble(),
      timeSpentSeconds: json['time_spent_seconds'],
      status: json['status'],
      mockTest: json['mock_test'] != null
          ? MockTest.fromJson(json['mock_test'])
          : null,
      answers: json['answers'] != null
          ? (json['answers'] as List)
              .map((a) => AttemptAnswer.fromJson(a))
              .toList()
          : null,
    );
  }
}

class AttemptAnswer {
  final int id;
  final int attemptId;
  final int questionId;
  final List<int>? selectedOptionIds;
  final bool? isCorrect;
  final int timeSpentOnQuestion;
  final Question? question;

  AttemptAnswer({
    required this.id,
    required this.attemptId,
    required this.questionId,
    this.selectedOptionIds,
    this.isCorrect,
    required this.timeSpentOnQuestion,
    this.question,
  });

  factory AttemptAnswer.fromJson(Map<String, dynamic> json) {
    return AttemptAnswer(
      id: json['id'],
      attemptId: json['attempt_id'],
      questionId: json['question_id'],
      selectedOptionIds: json['selected_option_ids'] != null
          ? List<int>.from(json['selected_option_ids'])
          : null,
      isCorrect: json['is_correct'],
      timeSpentOnQuestion: json['time_spent_on_question'],
      question: json['question'] != null
          ? Question.fromJson(json['question'])
          : null,
    );
  }
}
