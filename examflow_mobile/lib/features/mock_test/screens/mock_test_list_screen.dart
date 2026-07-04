import 'package:flutter/material.dart';
import '../../../core/services/api_service.dart';
import '../../../core/constants/api_constants.dart';
import '../../../core/models/models.dart';
import 'instructions_screen.dart';

class MockTestListScreen extends StatefulWidget {
  const MockTestListScreen({super.key});

  @override
  State<MockTestListScreen> createState() => _MockTestListScreenState();
}

class _MockTestListScreenState extends State<MockTestListScreen> {
  List<MockTest> _mockTests = [];
  bool _isLoading = true;
  String? _selectedExamFilter;

  @override
  void initState() {
    super.initState();
    _loadMockTests();
  }

  Future<void> _loadMockTests() async {
    try {
      final response = await ApiService.get(ApiConstants.mockTests);
      if (mounted) {
        setState(() {
          _mockTests = (response['data']['mock_tests'] as List)
              .map((m) => MockTest.fromJson(m))
              .toList();
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isLoading = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error loading mock tests: $e')),
        );
      }
    }
  }

  Color _getDifficultyColor(String difficulty) {
    switch (difficulty) {
      case 'easy': return Colors.green;
      case 'hard': return Colors.red;
      default: return Colors.orange;
    }
  }

  @override
  Widget build(BuildContext context) {
    final filteredTests = _selectedExamFilter != null
        ? _mockTests.where((t) => t.exam?.name == _selectedExamFilter).toList()
        : _mockTests;

    final examNames = _mockTests.map((t) => t.exam?.name).whereType<String>().toSet().toList()..sort();

    if (_isLoading) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }

    return Scaffold(
      appBar: AppBar(title: const Text('Mock Tests')),
      body: _mockTests.isEmpty
          ? const Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.quiz, size: 64, color: Colors.grey),
                  SizedBox(height: 16),
                  Text('No mock tests available', style: TextStyle(fontSize: 18, color: Colors.grey)),
                  SizedBox(height: 8),
                  Text('Check back later for new tests', style: TextStyle(color: Colors.grey)),
                ],
              ),
            )
          : Column(
              children: [
                if (examNames.length > 1)
                  SizedBox(
                    height: 50,
                    child: ListView(
                      scrollDirection: Axis.horizontal,
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                      children: [
                        _buildFilterChip('All', _selectedExamFilter == null, () {
                          setState(() => _selectedExamFilter = null);
                        }),
                        ...examNames.map((name) => _buildFilterChip(
                          name,
                          _selectedExamFilter == name,
                          () => setState(() => _selectedExamFilter = name),
                        )),
                      ],
                    ),
                  ),
                Expanded(
                  child: RefreshIndicator(
                    onRefresh: _loadMockTests,
                    child: ListView.builder(
                      padding: const EdgeInsets.all(16),
                      itemCount: filteredTests.length,
                      itemBuilder: (context, index) => _buildMockTestCard(filteredTests[index]),
                    ),
                  ),
                ),
              ],
            ),
    );
  }

  Widget _buildFilterChip(String label, bool selected, VoidCallback onTap) {
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: FilterChip(
        label: Text(label),
        selected: selected,
        onSelected: (_) => onTap(),
        selectedColor: Theme.of(context).colorScheme.primary,
        labelStyle: TextStyle(
          color: selected ? Colors.white : Colors.grey[700],
          fontWeight: FontWeight.w500,
        ),
      ),
    );
  }

  Widget _buildMockTestCard(MockTest mockTest) {
    final sections = mockTest.sections ?? [];
    final qCount = mockTest.questions?.length ?? mockTest.totalQuestions;
    final difficultyColor = _getDifficultyColor(mockTest.difficulty);

    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: InkWell(
        onTap: () => _showStartTestDialog(mockTest),
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Expanded(
                              child: Text(
                                mockTest.title,
                                style: const TextStyle(fontSize: 17, fontWeight: FontWeight.bold),
                              ),
                            ),
                            if (mockTest.isOfficial)
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                decoration: BoxDecoration(
                                  color: Colors.blue.withOpacity(0.1),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: const Text('OFFICIAL', style: TextStyle(color: Colors.blue, fontSize: 10, fontWeight: FontWeight.bold)),
                              ),
                          ],
                        ),
                        if (mockTest.exam != null)
                          Text(
                            mockTest.exam!.name,
                            style: TextStyle(fontSize: 13, color: Colors.grey[500]),
                          ),
                      ],
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: difficultyColor.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(
                      mockTest.difficulty.toUpperCase(),
                      style: TextStyle(color: difficultyColor, fontSize: 11, fontWeight: FontWeight.bold),
                    ),
                  ),
                ],
              ),
              if (mockTest.description != null) ...[
                const SizedBox(height: 8),
                Text(mockTest.description!, style: TextStyle(color: Colors.grey[600], fontSize: 13), maxLines: 2, overflow: TextOverflow.ellipsis),
              ],
              const SizedBox(height: 12),
              Row(
                children: [
                  _buildInfoChip(Icons.timer, '${mockTest.durationMinutes} min'),
                  const SizedBox(width: 8),
                  _buildInfoChip(Icons.star, '${mockTest.totalMarks} marks'),
                  const SizedBox(width: 8),
                  _buildInfoChip(Icons.quiz, '$qCount Q'),
                  if (mockTest.negativeMarking) ...[
                    const SizedBox(width: 8),
                    _buildInfoChip(Icons.remove_circle_outline, '-${mockTest.negativeMarkingValue}'),
                  ],
                ],
              ),
              if (sections.isNotEmpty) ...[
                const SizedBox(height: 12),
                const Divider(height: 1),
                const SizedBox(height: 8),
                Text(
                  'Sections',
                  style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Colors.grey[700]),
                ),
                const SizedBox(height: 6),
                Wrap(
                  spacing: 6,
                  runSpacing: 6,
                  children: sections.map((s) => Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: Theme.of(context).colorScheme.primary.withOpacity(0.06),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(
                      '${s.name} (${s.totalQuestions}q/${s.totalMarks}m)',
                      style: TextStyle(fontSize: 11, color: Theme.of(context).colorScheme.primary),
                    ),
                  )).toList(),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildInfoChip(IconData icon, String label) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(color: Colors.grey[100], borderRadius: BorderRadius.circular(6)),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 13, color: Colors.grey[600]),
          const SizedBox(width: 3),
          Text(label, style: TextStyle(fontSize: 11, color: Colors.grey[600])),
        ],
      ),
    );
  }

  void _showStartTestDialog(MockTest mockTest) {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => InstructionsScreen(mockTestId: mockTest.id)),
    );
  }
}
