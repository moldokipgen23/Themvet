import 'package:flutter/material.dart';
import '../../../core/models/models.dart';
import '../../../core/services/api_service.dart';
import '../../../core/constants/api_constants.dart';
import 'practice_screen.dart';

class TopicSelectionScreen extends StatefulWidget {
  final Exam exam;
  final Subject subject;

  const TopicSelectionScreen({
    super.key,
    required this.exam,
    required this.subject,
  });

  @override
  State<TopicSelectionScreen> createState() => _TopicSelectionScreenState();
}

class _TopicSelectionScreenState extends State<TopicSelectionScreen> {
  List<Topic> _topics = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadTopics();
  }

  Future<void> _loadTopics() async {
    try {
      final response = await ApiService.get(
        '${ApiConstants.exams}/${widget.exam.id}/subjects/${widget.subject.id}/topics',
      );
      if (mounted) {
        setState(() {
          _topics = (response['data']['topics'] as List)
              .map((e) => Topic.fromJson(e))
              .toList();
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isLoading = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error loading topics: $e')),
        );
      }
    }
  }

  void _startPractice({Topic? topic}) {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => PracticeScreen(
          examId: widget.exam.id,
          subjectId: widget.subject.id,
          topicId: topic?.id,
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('${widget.subject.name} - Topics'),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : Column(
              children: [
                // Practice All button
                Padding(
                  padding: const EdgeInsets.all(16),
                  child: SizedBox(
                    width: double.infinity,
                    child: ElevatedButton.icon(
                      onPressed: () => _startPractice(),
                      icon: const Icon(Icons.play_arrow),
                      label: Text('Practice All ${widget.subject.name} Questions'),
                      style: ElevatedButton.styleFrom(
                        padding: const EdgeInsets.all(16),
                      ),
                    ),
                  ),
                ),
                // Topics list
                Expanded(
                  child: _topics.isEmpty
                      ? const Center(child: Text('No topics available'))
                      : ListView.builder(
                          padding: const EdgeInsets.symmetric(horizontal: 16),
                          itemCount: _topics.length,
                          itemBuilder: (context, index) {
                            final topic = _topics[index];
                            return Card(
                              margin: const EdgeInsets.only(bottom: 8),
                              child: ListTile(
                                leading: CircleAvatar(
                                  backgroundColor: Colors.orange.withOpacity(0.1),
                                  child: const Icon(Icons.topic, color: Colors.orange),
                                ),
                                title: Text(topic.name),
                                trailing: const Icon(Icons.chevron_right),
                                onTap: () => _startPractice(topic: topic),
                              ),
                            );
                          },
                        ),
                ),
              ],
            ),
    );
  }
}
