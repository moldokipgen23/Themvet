import 'package:flutter/material.dart';
import '../../../core/models/models.dart';
import '../../../routes/app_routes.dart';

class ResultScreen extends StatelessWidget {
  const ResultScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final args = ModalRoute.of(context)?.settings.arguments;
    if (args == null || args is! Map<String, dynamic>) {
      return Scaffold(
        appBar: AppBar(title: const Text('Error')),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.error_outline, size: 64, color: Colors.red[300]),
              const SizedBox(height: 16),
              const Text('No result data found'),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: () => Navigator.of(context).popUntil((route) => route.isFirst),
                child: const Text('Back to Home'),
              ),
            ],
          ),
        ),
      );
    }

    final data = args;
    final summary = data['summary'];
    final attempt = data['attempt'];
    final gamification = data['gamification'];
    final mockTestId = data['mock_test_id'] ?? attempt?['mock_test_id'];

    return Scaffold(
      appBar: AppBar(
        title: const Text('Test Result'),
        automaticallyImplyLeading: false,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            // Gamification Popup
            if (gamification != null) _buildGamificationCard(context, gamification),
            // Score Card
            Card(
              child: Padding(
                padding: const EdgeInsets.all(24),
                child: Column(
                  children: [
                    SizedBox(
                      width: 150,
                      height: 150,
                      child: Stack(
                        fit: StackFit.expand,
                        children: [
                          CircularProgressIndicator(
                            value: (summary['accuracy'] ?? 0) / 100,
                            strokeWidth: 12,
                            backgroundColor: Colors.grey[200],
                            valueColor: AlwaysStoppedAnimation<Color>(
                              _getScoreColor(summary['accuracy'] ?? 0),
                            ),
                          ),
                          Center(
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Text(
                                  '${summary['accuracy']?.toStringAsFixed(1) ?? '0'}%',
                                  style: const TextStyle(
                                    fontSize: 32,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                                const Text(
                                  'Accuracy',
                                  style: TextStyle(color: Colors.grey, fontSize: 14),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 24),
                    Text(
                      'Score: ${summary['score']?.toStringAsFixed(1) ?? '0'}',
                      style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      'out of ${summary['total_questions'] ?? 0} questions',
                      style: TextStyle(color: Colors.grey[600]),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                Expanded(child: _buildStatCard('Correct', '${summary['correct'] ?? 0}', Colors.green, Icons.check_circle)),
                const SizedBox(width: 12),
                Expanded(child: _buildStatCard('Wrong', '${summary['wrong'] ?? 0}', Colors.red, Icons.cancel)),
              ],
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(child: _buildStatCard('Unattempted', '${summary['unattempted'] ?? 0}', Colors.orange, Icons.help_outline)),
                const SizedBox(width: 12),
                Expanded(child: _buildStatCard('Time', _formatTime(summary['time_spent'] ?? 0), Colors.blue, Icons.timer)),
              ],
            ),
            const SizedBox(height: 24),
            // Topic-wise breakdown placeholder
            if (attempt != null) _buildTopicBreakdown(attempt),
            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              height: 50,
              child: ElevatedButton(
                onPressed: () => Navigator.of(context).popUntil((route) => route.isFirst),
                child: const Text('Back to Home'),
              ),
            ),
            const SizedBox(height: 12),
            SizedBox(
              width: double.infinity,
              height: 50,
              child: OutlinedButton(
                onPressed: attempt != null ? () {
                  final answers = (attempt['answers'] as List? ?? []).map((a) => AttemptAnswer.fromJson(a)).toList();
                  final questionsMap = <int, Question>{};
                  for (final a in answers) {
                    if (a.question != null) questionsMap[a.questionId] = a.question!;
                  }
                  Navigator.pushNamed(context, AppRoutes.reviewAnswers, arguments: {'answers': answers, 'questions': questionsMap});
                } : null,
                child: const Text('Review Answers'),
              ),
            ),
            if (mockTestId != null) ...[
              const SizedBox(height: 12),
              SizedBox(
                width: double.infinity,
                height: 50,
                child: OutlinedButton.icon(
                  onPressed: () {
                    Navigator.of(context).popUntil((route) => route.isFirst);
                    Navigator.pushNamed(context, AppRoutes.instructions, arguments: {'mock_test_id': mockTestId});
                  },
                  icon: const Icon(Icons.replay),
                  label: const Text('Retake Test'),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildGamificationCard(BuildContext context, Map gamification) {
    final points = gamification['points_earned'] ?? 0;
    final newBadges = gamification['new_badges'] as List? ?? [];

    return Card(
      color: Theme.of(context).colorScheme.primary.withOpacity(0.1),
      margin: const EdgeInsets.only(bottom: 16),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Icon(Icons.star, color: Colors.amber, size: 28),
                const SizedBox(width: 8),
                Text(
                  '+$points Points!',
                  style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: Colors.amber),
                ),
              ],
            ),
            if (newBadges.isNotEmpty) ...[
              const SizedBox(height: 12),
              const Text('New Badges Earned!', style: TextStyle(fontWeight: FontWeight.bold)),
              const SizedBox(height: 8),
              ...newBadges.map((badge) => Padding(
                padding: const EdgeInsets.symmetric(vertical: 4),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.emoji_events, color: Colors.amber, size: 20),
                    const SizedBox(width: 8),
                    Text(badge['name'] ?? '', style: const TextStyle(fontWeight: FontWeight.w500)),
                  ],
                ),
              )),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildStatCard(String title, String value, Color color, IconData icon) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            Icon(icon, color: color, size: 32),
            const SizedBox(height: 8),
            Text(value, style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: color)),
            const SizedBox(height: 4),
            Text(title, style: TextStyle(color: Colors.grey[600], fontSize: 12)),
          ],
        ),
      ),
    );
  }

  Widget _buildTopicBreakdown(Map attempt) {
    final answers = attempt['answers'] as List? ?? [];
    final topicMap = <String, Map<String, int>>{};

    for (final answer in answers) {
      final question = answer['question'];
      if (question == null) continue;
      final topicName = question['topic']?['name'] ?? 'General';
      topicMap.putIfAbsent(topicName, () => {'correct': 0, 'wrong': 0, 'total': 0});
      topicMap[topicName]!['total'] = topicMap[topicName]!['total']! + 1;
      if (answer['is_correct'] == true) {
        topicMap[topicName]!['correct'] = topicMap[topicName]!['correct']! + 1;
      } else {
        topicMap[topicName]!['wrong'] = topicMap[topicName]!['wrong']! + 1;
      }
    }

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Topic-wise Performance', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
            const SizedBox(height: 16),
            ...topicMap.entries.map((entry) {
              final stats = entry.value;
              final accuracy = stats['total']! > 0 ? (stats['correct']! / stats['total']!) * 100 : 0.0;
              return Padding(
                padding: const EdgeInsets.only(bottom: 12),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(entry.key, style: const TextStyle(fontWeight: FontWeight.w500)),
                        Text(
                          '${stats['correct']}/${stats['total']}',
                          style: TextStyle(color: _getScoreColor(accuracy), fontWeight: FontWeight.bold),
                        ),
                      ],
                    ),
                    const SizedBox(height: 4),
                    ClipRRect(
                      borderRadius: BorderRadius.circular(4),
                      child: LinearProgressIndicator(
                        value: accuracy / 100,
                        backgroundColor: Colors.grey[200],
                        color: _getScoreColor(accuracy),
                        minHeight: 6,
                      ),
                    ),
                  ],
                ),
              );
            }),
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

  String _formatTime(int seconds) {
    final minutes = seconds ~/ 60;
    final secs = seconds % 60;
    return '${minutes}m ${secs}s';
  }
}
