import 'package:flutter/material.dart';
import '../../../core/models/models.dart';
import 'topic_selection_screen.dart';

class SubjectSelectionScreen extends StatelessWidget {
  final Exam exam;

  const SubjectSelectionScreen({super.key, required this.exam});

  @override
  Widget build(BuildContext context) {
    final subjects = exam.subjects ?? [];

    return Scaffold(
      appBar: AppBar(title: Text('${exam.name} - Subjects')),
      body: subjects.isEmpty
          ? const Center(child: Text('No subjects available for this exam'))
          : ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: subjects.length,
              itemBuilder: (context, index) {
                final subject = subjects[index];
                return Card(
                  margin: const EdgeInsets.only(bottom: 12),
                  child: ListTile(
                    leading: CircleAvatar(
                      backgroundColor: Colors.green.withOpacity(0.1),
                      child: const Icon(Icons.book, color: Colors.green),
                    ),
                    title: Text(subject.name),
                    subtitle: Text('${subject.topics?.length ?? 0} topics'),
                    trailing: const Icon(Icons.chevron_right),
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => TopicSelectionScreen(
                            exam: exam,
                            subject: subject,
                          ),
                        ),
                      );
                    },
                  ),
                );
              },
            ),
    );
  }
}
