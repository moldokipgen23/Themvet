import 'package:flutter/material.dart';
import '../../../core/services/api_service.dart';
import '../../../core/constants/api_constants.dart';
import '../../../core/models/models.dart';
import '../../../routes/app_routes.dart';

class PracticeScreen extends StatefulWidget {
  final int? examId;
  final int? subjectId;
  final int? topicId;

  const PracticeScreen({
    super.key,
    this.examId,
    this.subjectId,
    this.topicId,
  });

  @override
  State<PracticeScreen> createState() => _PracticeScreenState();
}

class _PracticeScreenState extends State<PracticeScreen> {
  List<Exam> _exams = [];
  Exam? _selectedExam;
  Subject? _selectedSubject;
  Topic? _selectedTopic;
  String _selectedDifficulty = 'medium';
  List<Question> _questions = [];
  int _currentQuestionIndex = 0;
  bool _isLoading = true;
  bool _isQuizStarted = false;
  final Map<int, int?> _selectedAnswers = {};
  final Map<int, bool?> _answerResults = {};

  @override
  void initState() {
    super.initState();
    _loadExams();
  }

  Future<void> _loadExams() async {
    try {
      final response = await ApiService.get(ApiConstants.exams);
      if (mounted) {
        setState(() {
          _exams = (response['data']['exams'] as List)
              .map((e) => Exam.fromJson(e))
              .toList();
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isLoading = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error loading exams: $e')),
        );
      }
    }
  }

  Future<void> _loadQuestions() async {
    if (_selectedExam == null || _selectedSubject == null) return;

    setState(() => _isLoading = true);

    try {
      final params = {
        'exam_id': _selectedExam!.id.toString(),
        'subject_id': _selectedSubject!.id.toString(),
        'difficulty': _selectedDifficulty,
        'count': '10',
      };

      if (_selectedTopic != null) {
        params['topic_id'] = _selectedTopic!.id.toString();
      }

      final response = await ApiService.get(
        ApiConstants.questionsPractice,
        queryParams: params,
      );

      if (mounted) {
        setState(() {
          _questions = (response['data']['questions'] as List)
              .map((q) => Question.fromJson(q))
              .toList();
          _isLoading = false;
          _isQuizStarted = true;
          _currentQuestionIndex = 0;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isLoading = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error loading questions: $e')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading && _exams.isEmpty) {
      return const Scaffold(
        body: Center(child: CircularProgressIndicator()),
      );
    }

    if (_isQuizStarted && _questions.isNotEmpty) {
      return _buildQuizView();
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('Practice'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Exam Selection
            const Text(
              'Select Exam',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 8),
            DropdownButtonFormField<Exam>(
              value: _selectedExam,
              decoration: const InputDecoration(
                border: OutlineInputBorder(),
                contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              ),
              hint: const Text('Choose an exam'),
              items: _exams.map((exam) {
                return DropdownMenuItem<Exam>(
                  value: exam,
                  child: Text(exam.name),
                );
              }).toList(),
              onChanged: (exam) {
                setState(() {
                  _selectedExam = exam;
                  _selectedSubject = null;
                  _selectedTopic = null;
                });
              },
            ),
            const SizedBox(height: 24),

            // Subject Selection
            if (_selectedExam != null) ...[
              const Text(
                'Select Subject',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 8),
              DropdownButtonFormField<Subject>(
                value: _selectedSubject,
                decoration: const InputDecoration(
                  border: OutlineInputBorder(),
                  contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                ),
                hint: const Text('Choose a subject'),
                items: _selectedExam!.subjects?.map((subject) {
                  return DropdownMenuItem<Subject>(
                    value: subject,
                    child: Text(subject.name),
                  );
                }).toList() ?? [],
                onChanged: (subject) {
                  setState(() {
                    _selectedSubject = subject;
                    _selectedTopic = null;
                  });
                },
              ),
              const SizedBox(height: 24),
            ],

            // Topic Selection
            if (_selectedSubject != null) ...[
              const Text(
                'Select Topic (Optional)',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 8),
              DropdownButtonFormField<Topic>(
                value: _selectedTopic,
                decoration: const InputDecoration(
                  border: OutlineInputBorder(),
                  contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                ),
                hint: const Text('All topics'),
                items: [
                  const DropdownMenuItem<Topic>(
                    value: null,
                    child: Text('All Topics'),
                  ),
                  ...?_selectedSubject!.topics?.map((topic) {
                    return DropdownMenuItem<Topic>(
                      value: topic,
                      child: Text(topic.name),
                    );
                  }),
                ],
                onChanged: (topic) {
                  setState(() {
                    _selectedTopic = topic;
                  });
                },
              ),
              const SizedBox(height: 24),
            ],

            // Difficulty Selection
            const Text(
              'Difficulty Level',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 8),
            SegmentedButton<String>(
              segments: const [
                ButtonSegment(value: 'easy', label: Text('Easy')),
                ButtonSegment(value: 'medium', label: Text('Medium')),
                ButtonSegment(value: 'hard', label: Text('Hard')),
              ],
              selected: {_selectedDifficulty},
              onSelectionChanged: (Set<String> selected) {
                setState(() {
                  _selectedDifficulty = selected.first;
                });
              },
            ),
            const SizedBox(height: 32),

            // Start Button
            SizedBox(
              width: double.infinity,
              height: 50,
              child: ElevatedButton(
                onPressed: _selectedExam != null && _selectedSubject != null
                    ? _loadQuestions
                    : null,
                child: const Text(
                  'Start Practice',
                  style: TextStyle(fontSize: 16),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildQuizView() {
    final question = _questions[_currentQuestionIndex];
    final progress = (_currentQuestionIndex + 1) / _questions.length;

    return Scaffold(
      appBar: AppBar(
        title: Text('Question ${_currentQuestionIndex + 1}/${_questions.length}'),
        actions: [
          TextButton(
            onPressed: () {
              showDialog(
                context: context,
                builder: (ctx) => AlertDialog(
                  title: const Text('Exit Quiz?'),
                  content: const Text('Your progress will be lost. Are you sure?'),
                  actions: [
                    TextButton(onPressed: () => Navigator.of(ctx).pop(), child: const Text('Cancel')),
                    TextButton(
                      onPressed: () {
                        Navigator.of(ctx).pop();
                        setState(() {
                          _isQuizStarted = false;
                          _questions = [];
                        });
                      },
                      child: const Text('Exit', style: TextStyle(color: Colors.red)),
                    ),
                  ],
                ),
              );
            },
            child: const Text('Exit', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Progress Bar
            LinearProgressIndicator(
              value: progress,
              backgroundColor: Colors.grey[300],
            ),
            const SizedBox(height: 24),

            // Difficulty Badge
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
              decoration: BoxDecoration(
                color: _getDifficultyColor(question.difficulty).withOpacity(0.1),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Text(
                question.difficulty.toUpperCase(),
                style: TextStyle(
                  color: _getDifficultyColor(question.difficulty),
                  fontWeight: FontWeight.bold,
                  fontSize: 12,
                ),
              ),
            ),
            const SizedBox(height: 16),

            // Question Text
            Text(
              question.questionText,
              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w500),
            ),
            const SizedBox(height: 24),

            // Options
            ...?question.options?.map((option) {
              final selectedId = _selectedAnswers[question.id];
              final result = _answerResults[question.id];
              final isSelected = selectedId == option.id;

              return Padding(
                padding: const EdgeInsets.only(bottom: 12),
                child: OptionCard(
                  option: option,
                  isSelected: isSelected,
                  showResult: result != null,
                  onTap: result == null ? () {
                    setState(() {
                      _selectedAnswers[question.id] = option.id;
                      _answerResults[question.id] = option.isCorrect;
                    });
                  } : () {},
                ),
              );
            }),

            const SizedBox(height: 24),

            // Navigation Buttons
            Row(
              children: [
                if (_currentQuestionIndex > 0)
                  Expanded(
                    child: OutlinedButton(
                      onPressed: () {
                        setState(() {
                          _currentQuestionIndex--;
                        });
                      },
                      child: const Text('Previous'),
                    ),
                  ),
                if (_currentQuestionIndex > 0) const SizedBox(width: 12),
                Expanded(
                  child: ElevatedButton(
                    onPressed: () {
                      if (_currentQuestionIndex < _questions.length - 1) {
                        setState(() {
                          _currentQuestionIndex++;
                        });
                      } else {
                        _finishQuiz();
                      }
                    },
                    child: Text(
                      _currentQuestionIndex < _questions.length - 1
                          ? 'Next'
                          : 'Finish',
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  void _finishQuiz() {
    int correct = 0;
    int wrong = 0;
    int unattempted = 0;

    for (var i = 0; i < _questions.length; i++) {
      final q = _questions[i];
      final selected = _selectedAnswers[i];
      if (selected == null) {
        unattempted++;
      } else {
        final correctOption = q.options?.firstWhere(
          (o) => o.isCorrect == true,
          orElse: () => q.options!.first,
        );
        if (correctOption != null && selected == correctOption.id) {
          correct++;
        } else {
          wrong++;
        }
      }
    }

    final total = _questions.length;
    final accuracy = total > 0 ? (correct / total) * 100 : 0.0;

    Navigator.pushReplacementNamed(
      context,
      AppRoutes.result,
      arguments: {
        'summary': {
          'total_questions': total,
          'correct_answers': correct,
          'wrong_answers': wrong,
          'unattempted': unattempted,
          'accuracy': accuracy,
          'time_spent': 0,
          'score': correct,
          'total_marks': total,
        },
        'attempt': {
          'answers': [],
          'mock_test_id': null,
        },
        'gamification': null,
      },
    );
  }

  Color _getDifficultyColor(String difficulty) {
    switch (difficulty) {
      case 'easy':
        return Colors.green;
      case 'medium':
        return Colors.orange;
      case 'hard':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }
}

class OptionCard extends StatelessWidget {
  final QuestionOption option;
  final VoidCallback onTap;
  final bool isSelected;
  final bool showResult;

  const OptionCard({
    super.key,
    required this.option,
    required this.onTap,
    this.isSelected = false,
    this.showResult = false,
  });

  @override
  Widget build(BuildContext context) {
    Color? backgroundColor;
    Color? borderColor;

    if (showResult) {
      if (option.isCorrect) {
        backgroundColor = Colors.green.withOpacity(0.1);
        borderColor = Colors.green;
      } else if (isSelected) {
        backgroundColor = Colors.red.withOpacity(0.1);
        borderColor = Colors.red;
      }
    } else if (isSelected) {
      backgroundColor = Theme.of(context).colorScheme.primary.withOpacity(0.1);
      borderColor = Theme.of(context).colorScheme.primary;
    }

    return Card(
      color: backgroundColor,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
        side: BorderSide(
          color: borderColor ?? Colors.grey.shade300,
          width: isSelected || showResult ? 2 : 1,
        ),
      ),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              Container(
                width: 32,
                height: 32,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: isSelected
                      ? Theme.of(context).colorScheme.primary
                      : Colors.grey.shade200,
                ),
                child: Center(
                  child: Text(
                    String.fromCharCode(65 + option.order - 1),
                    style: TextStyle(
                      color: isSelected ? Colors.white : Colors.black,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Text(
                  option.optionText,
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                  ),
                ),
              ),
              if (showResult && option.isCorrect)
                const Icon(Icons.check_circle, color: Colors.green),
              if (showResult && isSelected && !option.isCorrect)
                const Icon(Icons.cancel, color: Colors.red),
            ],
          ),
        ),
      ),
    );
  }
}
