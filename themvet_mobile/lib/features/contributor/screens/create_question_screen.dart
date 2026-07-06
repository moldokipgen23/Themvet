import 'package:flutter/material.dart';
import '../../../core/services/api_service.dart';
import '../../../core/constants/api_constants.dart';
import '../../../core/models/models.dart';

class CreateQuestionScreen extends StatefulWidget {
  const CreateQuestionScreen({super.key});

  @override
  State<CreateQuestionScreen> createState() => _CreateQuestionScreenState();
}

class _CreateQuestionScreenState extends State<CreateQuestionScreen> {
  final _formKey = GlobalKey<FormState>();
  final _questionTextController = TextEditingController();
  final _explanationController = TextEditingController();
  final List<Map<String, dynamic>> _options = [
    {'text': '', 'isCorrect': false},
    {'text': '', 'isCorrect': false},
    {'text': '', 'isCorrect': false},
    {'text': '', 'isCorrect': false},
  ];

  List<Exam> _exams = [];
  Exam? _selectedExam;
  Subject? _selectedSubject;
  Topic? _selectedTopic;
  String _difficulty = 'medium';
  bool _isLoading = false;
  bool _isLoadingExams = true;

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
          _isLoadingExams = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isLoadingExams = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error loading exams: $e')),
        );
      }
    }
  }

  Future<void> _submitQuestion() async {
    if (!_formKey.currentState!.validate()) return;

    final hasCorrectOption = _options.any((o) => o['isCorrect'] && o['text'].isNotEmpty);
    if (!hasCorrectOption) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please mark at least one correct answer')),
      );
      return;
    }

    if (_selectedExam == null || _selectedSubject == null || _selectedTopic == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please select exam, subject, and topic')),
      );
      return;
    }

    setState(() => _isLoading = true);

    try {
      final validOptions = _options
          .where((o) => o['text'].isNotEmpty)
          .map((o) => {
                'option_text': o['text'],
                'is_correct': o['isCorrect'],
              })
          .toList();

      final response = await ApiService.post(
        ApiConstants.teacherQuestions,
        body: {
          'exam_id': _selectedExam!.id,
          'subject_id': _selectedSubject!.id,
          'topic_id': _selectedTopic!.id,
          'question_text': _questionTextController.text.trim(),
          'difficulty': _difficulty,
          'explanation': _explanationController.text.trim().isEmpty
              ? null
              : _explanationController.text.trim(),
          'options': validOptions,
        },
      );

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(response['message'] ?? 'Question submitted'),
            backgroundColor: Colors.green,
          ),
        );
        Navigator.pop(context);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e'), backgroundColor: Colors.red),
        );
      }
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Create Question'),
      ),
      body: _isLoadingExams
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Exam Selection
                    const Text('Select Exam *', style: TextStyle(fontWeight: FontWeight.bold)),
                    const SizedBox(height: 8),
                    DropdownButtonFormField<Exam>(
                      value: _selectedExam,
                      decoration: const InputDecoration(border: OutlineInputBorder()),
                      hint: const Text('Choose exam'),
                      items: _exams.map((e) => DropdownMenuItem(value: e, child: Text(e.name))).toList(),
                      onChanged: (val) {
                        setState(() {
                          _selectedExam = val;
                          _selectedSubject = null;
                          _selectedTopic = null;
                        });
                      },
                      validator: (val) => val == null ? 'Required' : null,
                    ),
                    const SizedBox(height: 16),

                    // Subject Selection
                    if (_selectedExam != null) ...[
                      const Text('Select Subject *', style: TextStyle(fontWeight: FontWeight.bold)),
                      const SizedBox(height: 8),
                      DropdownButtonFormField<Subject>(
                        value: _selectedSubject,
                        decoration: const InputDecoration(border: OutlineInputBorder()),
                        hint: const Text('Choose subject'),
                        items: _selectedExam!.subjects
                            ?.map((s) => DropdownMenuItem(value: s, child: Text(s.name)))
                            .toList() ?? [],
                        onChanged: (val) {
                          setState(() {
                            _selectedSubject = val;
                            _selectedTopic = null;
                          });
                        },
                        validator: (val) => val == null ? 'Required' : null,
                      ),
                      const SizedBox(height: 16),
                    ],

                    // Topic Selection
                    if (_selectedSubject != null) ...[
                      const Text('Select Topic *', style: TextStyle(fontWeight: FontWeight.bold)),
                      const SizedBox(height: 8),
                      DropdownButtonFormField<Topic>(
                        value: _selectedTopic,
                        decoration: const InputDecoration(border: OutlineInputBorder()),
                        hint: const Text('Choose topic'),
                        items: _selectedSubject!.topics
                            ?.map((t) => DropdownMenuItem(value: t, child: Text(t.name)))
                            .toList() ?? [],
                        onChanged: (val) => setState(() => _selectedTopic = val),
                        validator: (val) => val == null ? 'Required' : null,
                      ),
                      const SizedBox(height: 16),
                    ],

                    // Difficulty
                    const Text('Difficulty *', style: TextStyle(fontWeight: FontWeight.bold)),
                    const SizedBox(height: 8),
                    SegmentedButton<String>(
                      segments: const [
                        ButtonSegment(value: 'easy', label: Text('Easy')),
                        ButtonSegment(value: 'medium', label: Text('Medium')),
                        ButtonSegment(value: 'hard', label: Text('Hard')),
                      ],
                      selected: {_difficulty},
                      onSelectionChanged: (val) => setState(() => _difficulty = val.first),
                    ),
                    const SizedBox(height: 16),

                    // Question Text
                    const Text('Question Text *', style: TextStyle(fontWeight: FontWeight.bold)),
                    const SizedBox(height: 8),
                    TextFormField(
                      controller: _questionTextController,
                      maxLines: 3,
                      decoration: const InputDecoration(
                        border: OutlineInputBorder(),
                        hintText: 'Enter your question here...',
                      ),
                      validator: (val) {
                        if (val == null || val.trim().isEmpty) return 'Required';
                        if (val.trim().length < 10) return 'Question too short';
                        return null;
                      },
                    ),
                    const SizedBox(height: 16),

                    // Options
                    const Text('Options *', style: TextStyle(fontWeight: FontWeight.bold)),
                    const SizedBox(height: 8),
                    ...List.generate(_options.length, (index) {
                      return Padding(
                        padding: const EdgeInsets.only(bottom: 8),
                        child: Row(
                          children: [
                            Radio<bool>(
                              value: true,
                              groupValue: _options[index]['isCorrect'],
                              onChanged: (val) {
                                setState(() {
                                  for (var i = 0; i < _options.length; i++) {
                                    _options[i]['isCorrect'] = false;
                                  }
                                  _options[index]['isCorrect'] = true;
                                });
                              },
                            ),
                            Expanded(
                              child: TextFormField(
                                initialValue: _options[index]['text'],
                                decoration: InputDecoration(
                                  border: const OutlineInputBorder(),
                                  labelText: 'Option ${String.fromCharCode(65 + index)}',
                                  hintText: index == 0 ? 'Correct answer' : 'Wrong answer ${index}',
                                ),
                                onChanged: (val) => _options[index]['text'] = val,
                                validator: index < 2
                                    ? (val) => val == null || val.trim().isEmpty ? 'Required' : null
                                    : null,
                              ),
                            ),
                          ],
                        ),
                      );
                    }),
                    const SizedBox(height: 8),
                    Text(
                      'Select the radio button next to the correct answer',
                      style: TextStyle(color: Colors.grey[600], fontSize: 12),
                    ),
                    const SizedBox(height: 16),

                    // Explanation
                    const Text('Explanation (Optional)', style: TextStyle(fontWeight: FontWeight.bold)),
                    const SizedBox(height: 8),
                    TextFormField(
                      controller: _explanationController,
                      maxLines: 3,
                      decoration: const InputDecoration(
                        border: OutlineInputBorder(),
                        hintText: 'Explain why the answer is correct...',
                      ),
                    ),
                    const SizedBox(height: 24),

                    // Submit Button
                    SizedBox(
                      width: double.infinity,
                      height: 50,
                      child: ElevatedButton(
                        onPressed: _isLoading ? null : _submitQuestion,
                        child: _isLoading
                            ? const SizedBox(
                                height: 20,
                                width: 20,
                                child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                              )
                            : const Text('Submit for Review', style: TextStyle(fontSize: 16)),
                      ),
                    ),
                  ],
                ),
              ),
            ),
    );
  }

  @override
  void dispose() {
    _questionTextController.dispose();
    _explanationController.dispose();
    super.dispose();
  }
}
