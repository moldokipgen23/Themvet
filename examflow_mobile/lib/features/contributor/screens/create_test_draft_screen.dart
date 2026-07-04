import 'package:flutter/material.dart';
import '../../../core/services/api_service.dart';
import '../../../core/constants/api_constants.dart';
import '../../../core/models/models.dart';

class CreateTestDraftScreen extends StatefulWidget {
  const CreateTestDraftScreen({super.key});

  @override
  State<CreateTestDraftScreen> createState() => _CreateTestDraftScreenState();
}

class _CreateTestDraftScreenState extends State<CreateTestDraftScreen> {
  final _formKey = GlobalKey<FormState>();
  final _titleController = TextEditingController();
  final _descriptionController = TextEditingController();
  final _durationController = TextEditingController(text: '60');
  final _totalMarksController = TextEditingController(text: '100');
  final _negMarkingController = TextEditingController(text: '0.25');

  List<Exam> _exams = [];
  Exam? _selectedExam;
  ExamPattern? _selectedPattern;
  bool _negativeMarking = true;
  bool _isLoading = true;
  bool _isSubmitting = false;
  List<_SectionEntry> _sections = [];

  @override
  void initState() {
    super.initState();
    _loadExams();
  }

  @override
  void dispose() {
    _titleController.dispose();
    _descriptionController.dispose();
    _durationController.dispose();
    _totalMarksController.dispose();
    _negMarkingController.dispose();
    super.dispose();
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

  void _applyPattern(ExamPattern? pattern) {
    setState(() {
      _selectedPattern = pattern;
      if (pattern != null) {
        _durationController.text = pattern.durationMinutes.toString();
        _totalMarksController.text = pattern.totalMarks.toString();
        _negMarkingController.text = pattern.negativeMarkingValue.toString();
        _negativeMarking = pattern.negativeMarking;
        _sections = pattern.sections?.map((s) => _SectionEntry(
          name: s.name,
          totalQuestions: s.totalQuestions,
          totalMarks: s.totalMarks,
          durationMinutes: s.durationMinutes,
          marksPerQuestion: s.marksPerQuestion,
          negativeMarksPerQuestion: s.negativeMarksPerQuestion,
        )).toList() ?? [];
      } else {
        _sections = [];
      }
    });
  }

  Future<void> _submitDraft() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isSubmitting = true);

    try {
      final body = <String, dynamic>{
        'exam_id': _selectedExam!.id,
        'title': _titleController.text,
        'description': _descriptionController.text.isNotEmpty
            ? _descriptionController.text
            : null,
        'duration_minutes': int.tryParse(_durationController.text) ?? 60,
        'total_marks': int.tryParse(_totalMarksController.text) ?? 100,
        'total_questions': _sections.fold(0, (sum, s) => sum + s.totalQuestions),
        'difficulty': 'medium',
        'negative_marking': _negativeMarking,
        'negative_marking_value': double.tryParse(_negMarkingController.text) ?? 0.25,
      };

      if (_selectedPattern != null) {
        body['exam_pattern_id'] = _selectedPattern!.id;
      }

      if (_sections.isNotEmpty) {
        body['sections'] = _sections.map((s) => {
          'name': s.name,
          'total_questions': s.totalQuestions,
          'total_marks': s.totalMarks,
          'duration_minutes': s.durationMinutes,
          'marks_per_question': s.marksPerQuestion,
          'negative_marks_per_question': s.negativeMarksPerQuestion,
          'is_mandatory': true,
        }).toList();
      }

      final response = await ApiService.post(
        ApiConstants.teacherMockTests,
        body: body,
      );

      if (mounted) {
        if (response['status'] == 'success') {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Test draft created! Now add questions to it.')),
          );
          Navigator.pop(context);
        } else {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(response['message'] ?? 'Failed to create draft')),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e')),
        );
      }
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }

    return Scaffold(
      appBar: AppBar(title: const Text('Create Mock Test')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Exam Selection
              DropdownButtonFormField<Exam>(
                value: _selectedExam,
                decoration: const InputDecoration(
                  labelText: 'Exam *',
                  border: OutlineInputBorder(),
                ),
                items: _exams.map((exam) => DropdownMenuItem(
                  value: exam,
                  child: Text(exam.name),
                )).toList(),
                onChanged: (value) {
                  setState(() {
                    _selectedExam = value;
                    _selectedPattern = null;
                    _sections = [];
                  });
                },
                validator: (v) => v == null ? 'Select an exam' : null,
              ),
              const SizedBox(height: 16),

              // Pattern Selection
              if (_selectedExam != null && _selectedExam!.patterns != null && _selectedExam!.patterns!.isNotEmpty) ...[
                DropdownButtonFormField<ExamPattern>(
                  value: _selectedPattern,
                  decoration: const InputDecoration(
                    labelText: 'Use Exam Pattern (Optional)',
                    hintText: 'Auto-fill settings from official pattern',
                    border: OutlineInputBorder(),
                  ),
                  items: _selectedExam!.patterns!.map((p) => DropdownMenuItem(
                    value: p,
                    child: Text(p.name, style: const TextStyle(fontSize: 13)),
                  )).toList(),
                  onChanged: _applyPattern,
                ),
                const SizedBox(height: 16),
              ],

              // Title
              TextFormField(
                controller: _titleController,
                decoration: const InputDecoration(
                  labelText: 'Test Title *',
                  hintText: 'e.g., SSC CGL Tier 1 Mock Test - 2',
                  border: OutlineInputBorder(),
                ),
                validator: (v) => v?.isEmpty ?? true ? 'Enter a title' : null,
              ),
              const SizedBox(height: 16),

              // Description
              TextFormField(
                controller: _descriptionController,
                decoration: const InputDecoration(
                  labelText: 'Description',
                  border: OutlineInputBorder(),
                ),
                maxLines: 2,
              ),
              const SizedBox(height: 16),

              // Duration, Marks, Neg Marking
              Row(
                children: [
                  Expanded(
                    child: TextFormField(
                      controller: _durationController,
                      decoration: const InputDecoration(labelText: 'Duration (min)', border: OutlineInputBorder()),
                      keyboardType: TextInputType.number,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: TextFormField(
                      controller: _totalMarksController,
                      decoration: const InputDecoration(labelText: 'Total Marks', border: OutlineInputBorder()),
                      keyboardType: TextInputType.number,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: SwitchListTile(
                      title: const Text('Negative Marking', style: TextStyle(fontSize: 14)),
                      value: _negativeMarking,
                      onChanged: (v) => setState(() => _negativeMarking = v),
                      contentPadding: EdgeInsets.zero,
                    ),
                  ),
                  if (_negativeMarking)
                    SizedBox(
                      width: 100,
                      child: TextFormField(
                        controller: _negMarkingController,
                        decoration: const InputDecoration(labelText: 'Neg Marks', border: OutlineInputBorder(), isDense: true),
                        keyboardType: TextInputType.number,
                      ),
                    ),
                ],
              ),
              const SizedBox(height: 16),

              // Sections
              Row(
                children: [
                  const Text('Sections', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                  const Spacer(),
                  TextButton.icon(
                    onPressed: () => _showAddSectionDialog(),
                    icon: const Icon(Icons.add, size: 18),
                    label: const Text('Add'),
                  ),
                ],
              ),
              if (_sections.isEmpty)
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: Colors.grey[50],
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: Colors.grey[300]!),
                  ),
                  child: const Center(
                    child: Text('No sections. Add sections or select a pattern above.', style: TextStyle(color: Colors.grey)),
                  ),
                )
              else
                ...List.generate(_sections.length, (i) {
                  final s = _sections[i];
                  return Card(
                    margin: const EdgeInsets.only(bottom: 8),
                    child: ListTile(
                      leading: CircleAvatar(
                        radius: 16,
                        child: Text('${i + 1}', style: const TextStyle(fontSize: 12)),
                      ),
                      title: Text(s.name, style: const TextStyle(fontWeight: FontWeight.w600)),
                      subtitle: Text('${s.totalQuestions}q | ${s.totalMarks}m | +${s.marksPerQuestion}/-${s.negativeMarksPerQuestion}'),
                      trailing: IconButton(
                        icon: const Icon(Icons.delete_outline, color: Colors.red, size: 20),
                        onPressed: () => setState(() => _sections.removeAt(i)),
                      ),
                    ),
                  );
                }),

              const SizedBox(height: 24),

              // Submit
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: _isSubmitting ? null : _submitDraft,
                  style: ElevatedButton.styleFrom(padding: const EdgeInsets.all(16)),
                  child: _isSubmitting
                      ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2))
                      : const Text('Create Mock Test', style: TextStyle(fontSize: 16)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _showAddSectionDialog() {
    final nameCtrl = TextEditingController();
    final qCtrl = TextEditingController(text: '25');
    final mCtrl = TextEditingController(text: '25');
    final marksPerQCtrl = TextEditingController(text: '1');
    final negMarksCtrl = TextEditingController(text: '0.25');

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Add Section'),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(controller: nameCtrl, decoration: const InputDecoration(labelText: 'Section Name *', border: OutlineInputBorder())),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(child: TextField(controller: qCtrl, decoration: const InputDecoration(labelText: 'Questions', border: OutlineInputBorder()), keyboardType: TextInputType.number)),
                  const SizedBox(width: 8),
                  Expanded(child: TextField(controller: mCtrl, decoration: const InputDecoration(labelText: 'Marks', border: OutlineInputBorder()), keyboardType: TextInputType.number)),
                ],
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(child: TextField(controller: marksPerQCtrl, decoration: const InputDecoration(labelText: 'Marks/Q', border: OutlineInputBorder()), keyboardType: TextInputType.number)),
                  const SizedBox(width: 8),
                  Expanded(child: TextField(controller: negMarksCtrl, decoration: const InputDecoration(labelText: 'Neg Marks/Q', border: OutlineInputBorder()), keyboardType: TextInputType.number)),
                ],
              ),
            ],
          ),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Cancel')),
          ElevatedButton(
            onPressed: () {
              if (nameCtrl.text.isNotEmpty) {
                setState(() {
                  _sections.add(_SectionEntry(
                    name: nameCtrl.text,
                    totalQuestions: int.tryParse(qCtrl.text) ?? 25,
                    totalMarks: int.tryParse(mCtrl.text) ?? 25,
                    marksPerQuestion: double.tryParse(marksPerQCtrl.text) ?? 1,
                    negativeMarksPerQuestion: double.tryParse(negMarksCtrl.text) ?? 0,
                  ));
                });
                Navigator.pop(ctx);
              }
            },
            child: const Text('Add'),
          ),
        ],
      ),
    );
  }
}

class _SectionEntry {
  final String name;
  final int totalQuestions;
  final int totalMarks;
  final int? durationMinutes;
  final double marksPerQuestion;
  final double negativeMarksPerQuestion;

  _SectionEntry({
    required this.name,
    required this.totalQuestions,
    required this.totalMarks,
    this.durationMinutes,
    this.marksPerQuestion = 1,
    this.negativeMarksPerQuestion = 0,
  });
}
