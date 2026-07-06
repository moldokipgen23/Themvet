import 'package:flutter/material.dart';
import '../../../core/services/api_service.dart';
import '../../../core/constants/api_constants.dart';

class AnalyticsScreen extends StatefulWidget {
  const AnalyticsScreen({super.key});

  @override
  State<AnalyticsScreen> createState() => _AnalyticsScreenState();
}

class _AnalyticsScreenState extends State<AnalyticsScreen> {
  Map<String, dynamic>? _progress;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadProgress();
  }

  Future<void> _loadProgress() async {
    try {
      final response = await ApiService.get(ApiConstants.resultsProgress);
      if (mounted) {
        setState(() {
          _progress = response['data'];
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isLoading = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error loading analytics: $e')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }

    final attempts = _progress?['attempts'] ?? [];
    final totalAttempts = _progress?['total_attempts'] ?? 0;
    final bestScore = _progress?['best_score'] ?? 0;
    final avgAccuracy = _progress?['average_accuracy'] ?? 0;
    final percentile = _progress?['percentile'];

    return Scaffold(
      appBar: AppBar(title: const Text('Performance Analytics')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Summary Cards
            Row(
              children: [
                Expanded(child: _buildStatCard('Tests Taken', '$totalAttempts', Colors.blue)),
                const SizedBox(width: 12),
                Expanded(child: _buildStatCard('Best Score', '$bestScore', Colors.green)),
              ],
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(child: _buildStatCard('Avg Accuracy', '${avgAccuracy.toStringAsFixed(1)}%', Colors.orange)),
                const SizedBox(width: 12),
                Expanded(child: _buildStatCard('Percentile', percentile != null ? '${percentile}%' : 'N/A', Colors.purple)),
              ],
            ),
            const SizedBox(height: 24),

            // Attempt History
            const Text(
              'Attempt History',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 12),

            if (attempts.isEmpty)
              const Card(
                child: Padding(
                  padding: EdgeInsets.all(32),
                  child: Center(
                    child: Text(
                      'No attempts yet. Take a mock test to see your analytics!',
                      textAlign: TextAlign.center,
                    ),
                  ),
                ),
              )
            else
              ...attempts.map((attempt) => Card(
                    margin: const EdgeInsets.only(bottom: 8),
                    child: ListTile(
                      leading: CircleAvatar(
                        backgroundColor: _getScoreColor(
                          attempt['accuracy'] ?? 0,
                        ).withOpacity(0.1),
                        child: Text(
                          '${attempt['accuracy'] ?? 0}%',
                          style: TextStyle(
                            color: _getScoreColor(attempt['accuracy'] ?? 0),
                            fontWeight: FontWeight.bold,
                            fontSize: 12,
                          ),
                        ),
                      ),
                      title: Text(attempt['test_title'] ?? 'Mock Test'),
                      subtitle: Text(
                        '${attempt['exam_name'] ?? ''} • ${attempt['date'] ?? ''}',
                      ),
                      trailing: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          Text(
                            'Score: ${attempt['score'] ?? 0}',
                            style: const TextStyle(fontWeight: FontWeight.bold),
                          ),
                          Text(
                            '${attempt['accuracy'] ?? 0}% accuracy',
                            style: TextStyle(
                              fontSize: 12,
                              color: Colors.grey[600],
                            ),
                          ),
                        ],
                      ),
                    ),
                  )),
          ],
        ),
      ),
    );
  }

  Widget _buildStatCard(String label, String value, Color color) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            Text(
              value,
              style: TextStyle(
                fontSize: 24,
                fontWeight: FontWeight.bold,
                color: color,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              label,
              style: TextStyle(
                fontSize: 12,
                color: Colors.grey[600],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Color _getScoreColor(double accuracy) {
    if (accuracy >= 80) return Colors.green;
    if (accuracy >= 60) return Colors.orange;
    return Colors.red;
  }
}
