import 'dart:async';
import 'package:flutter/material.dart';
import '../../../core/services/api_service.dart';
import '../../../core/constants/api_constants.dart';
import '../../../core/models/models.dart';

class MockTestPlayerScreen extends StatefulWidget {
  const MockTestPlayerScreen({super.key});

  @override
  State<MockTestPlayerScreen> createState() => _MockTestPlayerScreenState();
}

class _MockTestPlayerScreenState extends State<MockTestPlayerScreen> {
  late MockTest _mockTest;
  late int _attemptId;
  int _currentQuestionIndex = 0;
  int _currentSectionIndex = 0;
  Map<int, List<int>> _answers = {};
  Set<int> _markedForReview = {};
  Timer? _overallTimer;
  Timer? _sectionTimer;
  int _overallTimeRemaining = 0;
  Map<int, int> _sectionTimeRemaining = {};
  bool _isSubmitting = false;
  bool _showPalette = false;
  bool _hasShownFiveMinWarning = false;
  List<Map<String, dynamic>> _sections = [];
  List<MockTestQuestion> _allQuestions = [];
  List<MockTestQuestion> _currentSectionQuestions = [];

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    final args = ModalRoute.of(context)?.settings.arguments as Map<String, dynamic>;
    _mockTest = args['mock_test'] as MockTest;
    _attemptId = args['attempt_id'] as int;
    final existingAnswers = args['answers'] as List<dynamic>? ?? [];

    _overallTimeRemaining = _mockTest.durationMinutes * 60;

    _allQuestions = _mockTest.questions ?? [];
    _sections = (_mockTest.sections ?? []).map((s) => {
      'id': s.id,
      'name': s.name,
      'total_questions': s.totalQuestions,
      'total_marks': s.totalMarks,
      'duration_minutes': s.durationMinutes,
    }).toList();

    for (var answer in existingAnswers) {
      final questionId = answer['question_id'] as int;
      final selectedIds = List<int>.from(answer['selected_option_ids'] ?? []);
      if (selectedIds.isNotEmpty) {
        _answers[questionId] = selectedIds;
      }
      if (answer['is_marked_for_review'] == true) {
        _markedForReview.add(questionId);
      }
    }

    if (_sections.isNotEmpty) {
      for (var section in _sections) {
        final sectionId = section['id'];
        final duration = section['duration_minutes'];
        _sectionTimeRemaining[sectionId] = duration != null ? duration * 60 : _overallTimeRemaining;
      }
      _loadSectionQuestions(0);
    } else {
      _currentSectionQuestions = _allQuestions;
    }

    _startTimers();
  }

  void _loadSectionQuestions(int sectionIndex) {
    if (sectionIndex >= _sections.length) return;
    _currentSectionIndex = sectionIndex;
    final sectionId = _sections[sectionIndex]['id'];
    _currentSectionQuestions = _allQuestions.where((q) {
      return (q as dynamic).mockTestSectionId == sectionId;
    }).toList();
    _currentQuestionIndex = 0;
  }

  void _startTimers() {
    _overallTimer?.cancel();
    _sectionTimer?.cancel();

    _overallTimer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (_overallTimeRemaining > 0) {
        setState(() => _overallTimeRemaining--);
        if (_overallTimeRemaining == 300 && !_hasShownFiveMinWarning) {
          _hasShownFiveMinWarning = true;
          if (mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                content: Text('⚠️ 5 minutes remaining! Please submit soon.'),
                backgroundColor: Colors.orange,
                duration: Duration(seconds: 5),
              ),
            );
          }
        }
      } else {
        _overallTimer?.cancel();
        _sectionTimer?.cancel();
        _submitTest();
      }
    });

    if (_sections.isNotEmpty && _currentSectionIndex < _sections.length) {
      final sectionId = _sections[_currentSectionIndex]['id'];
      if (_sectionTimeRemaining[sectionId] != null) {
        _sectionTimer = Timer.periodic(const Duration(seconds: 1), (timer) {
          final currentSectionId = _sections[_currentSectionIndex]['id'];
          if (_sectionTimeRemaining.containsKey(currentSectionId) && (_sectionTimeRemaining[currentSectionId] ?? 0) > 0) {
            setState(() => _sectionTimeRemaining[currentSectionId] = (_sectionTimeRemaining[currentSectionId] ?? 1) - 1);
          } else {
            _nextSection();
          }
        });
      }
    }
  }

  void _restartSectionTimer() {
    _sectionTimer?.cancel();
    if (_currentSectionIndex < _sections.length) {
      final sectionId = _sections[_currentSectionIndex]['id'];
      if (_sectionTimeRemaining[sectionId] != null) {
        _sectionTimer = Timer.periodic(const Duration(seconds: 1), (timer) {
          final currentSectionId = _sections[_currentSectionIndex]['id'];
          if (_sectionTimeRemaining.containsKey(currentSectionId) && (_sectionTimeRemaining[currentSectionId] ?? 0) > 0) {
            setState(() => _sectionTimeRemaining[currentSectionId] = (_sectionTimeRemaining[currentSectionId] ?? 1) - 1);
          } else {
            _nextSection();
          }
        });
      }
    }
  }

  String _formatTime(int seconds) {
    final h = seconds ~/ 3600;
    final m = (seconds % 3600) ~/ 60;
    final s = seconds % 60;
    if (h > 0) {
      return '${h.toString().padLeft(2, '0')}:${m.toString().padLeft(2, '0')}:${s.toString().padLeft(2, '0')}';
    }
    return '${m.toString().padLeft(2, '0')}:${s.toString().padLeft(2, '0')}';
  }

  void _saveAnswer(int questionId, List<int> optionIds) async {
    _answers[questionId] = optionIds;
    setState(() {});

    try {
      await ApiService.post(
        '${ApiConstants.mockTests}/${_mockTest.id}/save-answer',
        body: {
          'attempt_id': _attemptId,
          'question_id': questionId,
          'selected_option_ids': optionIds,
          'is_marked_for_review': _markedForReview.contains(questionId),
        },
      );
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Failed to save answer: $e'),
            backgroundColor: Colors.red,
            duration: const Duration(seconds: 2),
          ),
        );
      }
    }
  }

  void _toggleReview(int questionId) async {
    if (_markedForReview.contains(questionId)) {
      _markedForReview.remove(questionId);
    } else {
      _markedForReview.add(questionId);
    }
    setState(() {});

    try {
      await ApiService.post(
        '${ApiConstants.mockTests}/${_mockTest.id}/toggle-review',
        body: {
          'attempt_id': _attemptId,
          'question_id': questionId,
        },
      );
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Failed to update review status: $e'),
            backgroundColor: Colors.red,
            duration: const Duration(seconds: 2),
          ),
        );
      }
    }
  }

  void _clearAnswer(int questionId) {
    _answers.remove(questionId);
    setState(() {});
  }

  void _nextSection() {
    if (_currentSectionIndex < _sections.length - 1) {
      _sectionTimer?.cancel();
      _loadSectionQuestions(_currentSectionIndex + 1);
      _restartSectionTimer();
      setState(() {});
    }
  }

  Color _getQuestionColor(int questionId) {
    if (_markedForReview.contains(questionId)) {
      if (_answers.containsKey(questionId)) return Colors.purple;
      return Colors.orange;
    }
    if (_answers.containsKey(questionId)) return Colors.green;
    return Colors.grey[300]!;
  }

  Color _getQuestionTextColor(int questionId) {
    if (_markedForReview.contains(questionId) || _answers.containsKey(questionId)) {
      return Colors.white;
    }
    return Colors.black87;
  }

  @override
  Widget build(BuildContext context) {
    if (_currentSectionQuestions.isEmpty) {
      return const Scaffold(body: Center(child: Text('No questions available')));
    }

    final currentQuestion = _currentSectionQuestions[_currentQuestionIndex];
    final mq = currentQuestion as dynamic;
    final question = mq.question;

    return PopScope(
      canPop: false,
      onPopInvokedWithResult: (didPop, result) {
        if (didPop) return;
        showDialog(
          context: context,
          builder: (ctx) => AlertDialog(
            title: const Text('Exit Test?'),
            content: const Text('Are you sure you want to exit? Your progress may be lost.'),
            actions: [
              TextButton(onPressed: () => Navigator.of(ctx).pop(), child: const Text('Cancel')),
              TextButton(
                onPressed: () {
                  Navigator.of(ctx).pop();
                  Navigator.of(context).pop();
                },
                child: const Text('Exit', style: TextStyle(color: Colors.red)),
              ),
            ],
          ),
        );
      },
      child: Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(_mockTest.title, style: const TextStyle(fontSize: 14)),
        automaticallyImplyLeading: false,
        actions: [
          // Section timer
          if (_sections.isNotEmpty && _currentSectionIndex < _sections.length)
            Container(
              margin: const EdgeInsets.only(right: 8),
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
              decoration: BoxDecoration(
                color: Colors.blue,
                borderRadius: BorderRadius.circular(16),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(Icons.hourglass_bottom, color: Colors.white, size: 16),
                  const SizedBox(width: 4),
                  Text(
                    _formatTime(_sectionTimeRemaining[_sections[_currentSectionIndex]['id']] ?? 0),
                    style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13),
                  ),
                ],
              ),
            ),
          // Overall timer
          Container(
            margin: const EdgeInsets.only(right: 12),
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
            decoration: BoxDecoration(
              color: _overallTimeRemaining < 300 ? Colors.red : Colors.grey[800],
              borderRadius: BorderRadius.circular(16),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(Icons.timer, color: Colors.white, size: 16),
                const SizedBox(width: 4),
                Text(
                  _formatTime(_overallTimeRemaining),
                  style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13),
                ),
              ],
            ),
          ),
        ],
      ),
      body: Column(
        children: [
          // Section Tabs
          if (_sections.isNotEmpty)
            Container(
              height: 44,
              color: Colors.white,
              child: ListView.builder(
                scrollDirection: Axis.horizontal,
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                itemCount: _sections.length,
                itemBuilder: (context, index) {
                  final section = _sections[index];
                  final isActive = index == _currentSectionIndex;
                  final sectionId = section['id'];
                  final sectionQs = _allQuestions.where((q) => (q as dynamic).mockTestSectionId == sectionId).toList();
                  final answered = sectionQs.where((q) => _answers.containsKey((q as dynamic).questionId)).length;
                  return GestureDetector(
                    onTap: () {
                      _sectionTimer?.cancel();
                      _loadSectionQuestions(index);
                      _restartSectionTimer();
                      setState(() {});
                    },
                    child: Container(
                      margin: const EdgeInsets.only(right: 8),
                      padding: const EdgeInsets.symmetric(horizontal: 14),
                      decoration: BoxDecoration(
                        color: isActive ? Theme.of(context).colorScheme.primary : Colors.grey[200],
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            section['name'],
                            style: TextStyle(
                              color: isActive ? Colors.white : Colors.black87,
                              fontWeight: FontWeight.w600,
                              fontSize: 13,
                            ),
                          ),
                          const SizedBox(width: 6),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1),
                            decoration: BoxDecoration(
                              color: isActive ? Colors.white24 : Colors.grey[400],
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              '$answered/${sectionQs.length}',
                              style: TextStyle(
                                color: isActive ? Colors.white : Colors.white,
                                fontSize: 11,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  );
                },
              ),
            ),

          // Legend
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            color: Colors.white,
            child: Row(
              children: [
                _legend(Colors.green, 'Answered'),
                _legend(Colors.grey[300]!, 'Not Answered'),
                _legend(Colors.orange, 'Marked'),
                _legend(Colors.purple, 'Answered+Marked'),
              ],
            ),
          ),

          // Question Number Grid (collapsible)
          if (_showPalette)
            Container(
              constraints: const BoxConstraints(maxHeight: 200),
              padding: const EdgeInsets.all(12),
              color: Colors.white,
              child: GridView.builder(
                shrinkWrap: true,
                gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                  crossAxisCount: 6,
                  mainAxisSpacing: 8,
                  crossAxisSpacing: 8,
                  childAspectRatio: 1.0,
                ),
                itemCount: _currentSectionQuestions.length,
                itemBuilder: (context, index) {
                  final qId = (_currentSectionQuestions[index] as dynamic).questionId;
                  return GestureDetector(
                    onTap: () {
                      setState(() {
                        _currentQuestionIndex = index;
                        _showPalette = false;
                      });
                    },
                    child: Container(
                      decoration: BoxDecoration(
                        color: _getQuestionColor(qId),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Center(
                        child: Text(
                          '${index + 1}',
                          style: TextStyle(
                            color: _getQuestionTextColor(qId),
                            fontSize: 12,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ),
                  );
                },
              ),
            ),

          // Question Content
          Expanded(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                        decoration: BoxDecoration(
                          color: Theme.of(context).colorScheme.primary.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Text(
                          'Q${_currentQuestionIndex + 1}',
                          style: TextStyle(color: Theme.of(context).colorScheme.primary, fontWeight: FontWeight.bold),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                        decoration: BoxDecoration(
                          color: Colors.grey[200],
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text('${mq.marks} marks', style: TextStyle(fontSize: 12, color: Colors.grey[700])),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  Text(question.questionText ?? '', style: const TextStyle(fontSize: 16, height: 1.5)),
                  const SizedBox(height: 20),
                  ...?question.options?.map((option) {
                    final selectedIds = _answers[mq.questionId] ?? [];
                    final isSelected = selectedIds.contains(option.id);
                    return Padding(
                      padding: const EdgeInsets.only(bottom: 10),
                      child: _buildOptionCard(option, isSelected, mq.questionId),
                    );
                  }),
                ],
              ),
            ),
          ),

          // Bottom Actions
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.white,
              boxShadow: [BoxShadow(color: Colors.grey.withOpacity(0.2), blurRadius: 5, offset: const Offset(0, -2))],
            ),
            child: Column(
              children: [
                Row(
                  children: [
                    // Previous
                    if (_currentQuestionIndex > 0)
                      Expanded(
                        child: OutlinedButton.icon(
                          onPressed: () => setState(() => _currentQuestionIndex--),
                          icon: const Icon(Icons.arrow_back, size: 16),
                          label: const Text('Prev', style: TextStyle(fontSize: 12)),
                        ),
                      ),
                    if (_currentQuestionIndex > 0) const SizedBox(width: 6),

                    // Mark for Review
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () => _toggleReview(_currentSectionQuestions[_currentQuestionIndex].questionId),
                        icon: Icon(
                          _markedForReview.contains(_currentSectionQuestions[_currentQuestionIndex].questionId)
                              ? Icons.bookmark
                              : Icons.bookmark_border,
                          size: 16,
                          color: Colors.orange,
                        ),
                        label: const Text('Review', style: TextStyle(fontSize: 12, color: Colors.orange)),
                      ),
                    ),
                    const SizedBox(width: 6),

                    // Clear
                    if (_answers.containsKey(_currentSectionQuestions[_currentQuestionIndex].questionId))
                      Expanded(
                        child: OutlinedButton(
                          onPressed: () => _clearAnswer(_currentSectionQuestions[_currentQuestionIndex].questionId),
                          style: OutlinedButton.styleFrom(foregroundColor: Colors.grey),
                          child: const Text('Clear', style: TextStyle(fontSize: 12)),
                        ),
                      ),
                    if (_answers.containsKey(_currentSectionQuestions[_currentQuestionIndex].questionId))
                      const SizedBox(width: 6),

                    // Save & Next
                    Expanded(
                      flex: 2,
                      child: ElevatedButton(
                        onPressed: _isSubmitting ? null : () {
                          if (_currentQuestionIndex < _currentSectionQuestions.length - 1) {
                            setState(() => _currentQuestionIndex++);
                          } else if (_currentSectionIndex < _sections.length - 1) {
                            _nextSection();
                          } else {
                            _showSubmitDialog();
                          }
                        },
                        child: Text(
                          _currentQuestionIndex < _currentSectionQuestions.length - 1
                              ? 'Save & Next'
                              : _currentSectionIndex < _sections.length - 1
                                  ? 'Next Section'
                                  : 'Submit',
                          style: const TextStyle(fontSize: 12),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                GestureDetector(
                  onTap: () => setState(() => _showPalette = !_showPalette),
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      color: Colors.grey[100],
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.grid_view, size: 16, color: Colors.grey[600]),
                        const SizedBox(width: 6),
                        Text(
                          '${_answers.length} answered  |  ${_markedForReview.length} marked',
                          style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                        ),
                        const SizedBox(width: 4),
                        Icon(_showPalette ? Icons.keyboard_arrow_up : Icons.keyboard_arrow_down, size: 16),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    ),
    );
  }

  Widget _legend(Color color, String label) {
    return Expanded(
      child: Row(
        children: [
          Container(width: 12, height: 12, decoration: BoxDecoration(color: color, borderRadius: BorderRadius.circular(3))),
          const SizedBox(width: 4),
          Text(label, style: const TextStyle(fontSize: 10)),
        ],
      ),
    );
  }

  Widget _buildOptionCard(dynamic option, bool isSelected, int questionId) {
    final labels = ['A', 'B', 'C', 'D', 'E', 'F'];
    final index = option.order != null ? option.order - 1 : 0;

    return Card(
      color: isSelected ? Theme.of(context).colorScheme.primary.withOpacity(0.08) : Colors.white,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
        side: BorderSide(
          color: isSelected ? Theme.of(context).colorScheme.primary : Colors.grey.shade200,
          width: isSelected ? 2 : 1,
        ),
      ),
      elevation: isSelected ? 2 : 0,
      child: InkWell(
        onTap: () {
          if (isSelected) {
            _clearAnswer(questionId);
          } else {
            _saveAnswer(questionId, [option.id]);
          }
        },
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: Row(
            children: [
              Container(
                width: 32,
                height: 32,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: isSelected ? Theme.of(context).colorScheme.primary : Colors.grey.shade100,
                ),
                child: Center(
                  child: Text(
                    labels[index < labels.length ? index : 0],
                    style: TextStyle(
                      color: isSelected ? Colors.white : Colors.black54,
                      fontWeight: FontWeight.bold,
                      fontSize: 13,
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Text(
                  option.optionText ?? '',
                  style: TextStyle(
                    fontSize: 15,
                    fontWeight: isSelected ? FontWeight.w600 : FontWeight.normal,
                  ),
                ),
              ),
              if (isSelected)
                Icon(Icons.check_circle, color: Theme.of(context).colorScheme.primary, size: 20),
            ],
          ),
        ),
      ),
    );
  }

  void _showSubmitDialog() {
    final total = _allQuestions.length;
    final answered = _answers.length;
    final marked = _markedForReview.length;
    final unattempted = total - answered;

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Submit Test?'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            _summaryRow('Total Questions', '$total', Colors.black),
            _summaryRow('Answered', '$answered', Colors.green),
            _summaryRow('Not Answered', '$unattempted', Colors.red),
            _summaryRow('Marked for Review', '$marked', Colors.orange),
            const SizedBox(height: 12),
            if (unattempted > 0)
              const Text(
                'You have unanswered questions. Submit anyway?',
                style: TextStyle(color: Colors.orange, fontSize: 13),
              ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Continue')),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(ctx);
              _submitTest();
            },
            child: const Text('Submit'),
          ),
        ],
      ),
    );
  }

  Widget _summaryRow(String label, String value, Color color) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: TextStyle(color: Colors.grey[600], fontSize: 14)),
          Text(value, style: TextStyle(color: color, fontWeight: FontWeight.bold, fontSize: 14)),
        ],
      ),
    );
  }

  Future<void> _submitTest() async {
    if (_isSubmitting) return;
    setState(() => _isSubmitting = true);
    _overallTimer?.cancel();
    _sectionTimer?.cancel();

    try {
      final answers = _answers.entries.map((entry) {
        return {
          'question_id': entry.key,
          'selected_option_ids': entry.value,
          'time_spent_on_question': 0,
        };
      }).toList();

      final response = await ApiService.post(
        '${ApiConstants.mockTests}/${_mockTest.id}/submit',
        body: {'attempt_id': _attemptId, 'answers': answers},
      );

      if (mounted) {
        Navigator.pushReplacementNamed(context, '/result', arguments: response['data']);
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isSubmitting = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e'), backgroundColor: Colors.red),
        );
      }
    }
  }

  @override
  void dispose() {
    _overallTimer?.cancel();
    _sectionTimer?.cancel();
    super.dispose();
  }
}
