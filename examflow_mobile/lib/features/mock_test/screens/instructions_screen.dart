import 'package:flutter/material.dart';
import '../../../core/services/api_service.dart';
import '../../../core/constants/api_constants.dart';
import '../../../core/models/models.dart';
import 'mock_test_player_screen.dart';

class InstructionsScreen extends StatefulWidget {
  final int mockTestId;

  const InstructionsScreen({super.key, required this.mockTestId});

  @override
  State<InstructionsScreen> createState() => _InstructionsScreenState();
}

class _InstructionsScreenState extends State<InstructionsScreen> {
  Map<String, dynamic>? _mockTest;
  bool _isLoading = true;
  bool _isStarting = false;
  bool _agreedToTerms = false;

  @override
  void initState() {
    super.initState();
    _loadMockTest();
  }

  Future<void> _loadMockTest() async {
    try {
      final response = await ApiService.get(
        '${ApiConstants.mockTests}/${widget.mockTestId}',
      );
      if (mounted) {
        setState(() {
          _mockTest = response['data']['mock_test'];
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isLoading = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error loading test: $e')),
        );
      }
    }
  }

  Future<void> _startTest() async {
    if (!_agreedToTerms) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please agree to the terms first')),
      );
      return;
    }

    if (_isStarting) return;
    setState(() => _isStarting = true);

    try {
      final response = await ApiService.post(
        '${ApiConstants.mockTests}/${widget.mockTestId}/start',
      );

      if (mounted && response['status'] == 'success') {
        final attempt = response['data']['attempt'];

        Navigator.pushReplacement(
          context,
          MaterialPageRoute(
            builder: (_) => MockTestPlayerScreen(),
            settings: RouteSettings(
              arguments: {
                'mock_test': MockTest.fromJson(_mockTest!),
                'attempt_id': attempt['id'],
              },
            ),
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isStarting = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error starting test: $e')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }

    if (_mockTest == null) {
      return const Scaffold(body: Center(child: Text('Test not found')));
    }

    final questions = _mockTest!['questions'] ?? [];

    return Scaffold(
      appBar: AppBar(title: const Text('Test Instructions')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Test Info Card
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      _mockTest!['title'] ?? 'Untitled Test',
                      style: const TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    if (_mockTest!['description'] != null) ...[
                      const SizedBox(height: 8),
                      Text(
                        _mockTest!['description'],
                        style: TextStyle(color: Colors.grey[600]),
                      ),
                    ],
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),

            // Test Details
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Test Details',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 12),
                    _buildDetailRow(
                      Icons.help_outline,
                      'Total Questions',
                      '${questions.length}',
                    ),
                    _buildDetailRow(
                      Icons.timer,
                      'Duration',
                      '${_mockTest!['duration_minutes'] ?? 60} minutes',
                    ),
                    _buildDetailRow(
                      Icons.score,
                      'Total Marks',
                      '${_mockTest!['total_marks'] ?? 100}',
                    ),
                    _buildDetailRow(
                      Icons.warning_amber,
                      'Negative Marking',
                      _mockTest!['negative_marking'] == true
                          ? 'Yes (-${_mockTest!['negative_marking_value'] ?? 0.25} per wrong answer)'
                          : 'No',
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),

            // Sections
            if (_mockTest!['sections'] != null && (_mockTest!['sections'] as List).isNotEmpty)
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Sections',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 12),
                      ...(_mockTest!['sections'] as List).map((section) => Padding(
                        padding: const EdgeInsets.only(bottom: 8),
                        child: Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: Theme.of(context).colorScheme.primary.withOpacity(0.04),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(
                              color: Theme.of(context).colorScheme.primary.withOpacity(0.1),
                            ),
                          ),
                          child: Row(
                            children: [
                              Container(
                                width: 4,
                                height: 40,
                                decoration: BoxDecoration(
                                  color: Theme.of(context).colorScheme.primary,
                                  borderRadius: BorderRadius.circular(2),
                                ),
                              ),
                              const SizedBox(width: 12),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      section['name'],
                                      style: const TextStyle(fontWeight: FontWeight.w600),
                                    ),
                                    Text(
                                      '${section['total_questions']} questions | ${section['total_marks']} marks',
                                      style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                                    ),
                                  ],
                                ),
                              ),
                              if (section['duration_minutes'] != null)
                                Text(
                                  '${section['duration_minutes']} min',
                                  style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                                ),
                            ],
                          ),
                        ),
                      )),
                    ],
                  ),
                ),
              ),
            const SizedBox(height: 16),

            // Instructions
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Instructions',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 12),
                    _buildInstruction(1, 'The test will start immediately after you click "Start Test".'),
                    _buildInstruction(2, 'The timer will start counting down from the moment you begin.'),
                    _buildInstruction(3, 'You can navigate between questions using the question palette.'),
                    _buildInstruction(4, 'Mark questions for review to revisit them later.'),
                    _buildInstruction(5, 'The test will auto-submit when the time expires.'),
                    _buildInstruction(6, 'Once submitted, you cannot change your answers.'),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),

            // Agreement Checkbox
            Card(
              child: CheckboxListTile(
                value: _agreedToTerms,
                onChanged: (value) {
                  setState(() => _agreedToTerms = value ?? false);
                },
                title: const Text(
                  'I have read and understood the instructions',
                  style: TextStyle(fontWeight: FontWeight.bold),
                ),
                controlAffinity: ListTileControlAffinity.leading,
              ),
            ),
            const SizedBox(height: 16),

            // Start Button
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: _agreedToTerms && !_isStarting ? _startTest : null,
                style: ElevatedButton.styleFrom(
                  padding: const EdgeInsets.all(16),
                  backgroundColor: Theme.of(context).colorScheme.primary,
                  foregroundColor: Colors.white,
                ),
                child: _isStarting
                    ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                    : const Text(
                        'Start Test',
                        style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                      ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDetailRow(IconData icon, String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        children: [
          Icon(icon, size: 20, color: Colors.grey[600]),
          const SizedBox(width: 12),
          Expanded(child: Text(label)),
          Text(
            value,
            style: const TextStyle(fontWeight: FontWeight.bold),
          ),
        ],
      ),
    );
  }

  Widget _buildInstruction(int number, String text) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          CircleAvatar(
            radius: 12,
            backgroundColor: Theme.of(context).colorScheme.primary.withOpacity(0.1),
            child: Text(
              '$number',
              style: TextStyle(
                fontSize: 12,
                color: Theme.of(context).colorScheme.primary,
                fontWeight: FontWeight.bold,
              ),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(child: Text(text)),
        ],
      ),
    );
  }
}
