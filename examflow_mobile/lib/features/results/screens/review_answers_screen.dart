import 'package:flutter/material.dart';
import '../../../core/models/models.dart';

class ReviewAnswersScreen extends StatelessWidget {
  final List<AttemptAnswer> answers;
  final Map<int, Question> questions;

  const ReviewAnswersScreen({
    super.key,
    required this.answers,
    required this.questions,
  });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Review Answers')),
      body: answers.isEmpty
          ? const Center(child: Text('No answers to review'))
          : ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: answers.length,
              itemBuilder: (context, index) {
                final answer = answers[index];
                final question = questions[answer.questionId];
                final isCorrect = answer.isCorrect;

                return Card(
                  margin: const EdgeInsets.only(bottom: 16),
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Question number and status
                        Row(
                          children: [
                            CircleAvatar(
                              radius: 16,
                              backgroundColor: isCorrect == true
                                  ? Colors.green.withOpacity(0.1)
                                  : Colors.red.withOpacity(0.1),
                              child: Text(
                                '${index + 1}',
                                style: TextStyle(
                                  color: isCorrect == true ? Colors.green : Colors.red,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Text(
                                question?.questionText ?? 'Question ${answer.questionId}',
                                style: const TextStyle(
                                  fontWeight: FontWeight.bold,
                                  fontSize: 16,
                                ),
                              ),
                            ),
                            Icon(
                              isCorrect == true ? Icons.check_circle : Icons.cancel,
                              color: isCorrect == true ? Colors.green : Colors.red,
                            ),
                          ],
                        ),
                        const SizedBox(height: 12),

                        // Options
                        if (question?.options != null)
                          ...question!.options!.map((option) {
                            final isSelected = answer.selectedOptionIds?.contains(option.id) == true;
                            final isCorrectOption = option.isCorrect == true;

                            Color? bgColor;
                            if (isCorrectOption) {
                              bgColor = Colors.green.withOpacity(0.1);
                            } else if (isSelected && !isCorrectOption) {
                              bgColor = Colors.red.withOpacity(0.1);
                            }

                            return Container(
                              margin: const EdgeInsets.only(bottom: 8),
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(
                                color: bgColor,
                                borderRadius: BorderRadius.circular(8),
                                border: Border.all(
                                  color: isCorrectOption
                                      ? Colors.green
                                      : isSelected
                                          ? Colors.red
                                          : Colors.grey.withOpacity(0.3),
                                ),
                              ),
                              child: Row(
                                children: [
                                  Icon(
                                    isCorrectOption
                                        ? Icons.check_circle
                                        : isSelected
                                            ? Icons.cancel
                                            : Icons.radio_button_unchecked,
                                    color: isCorrectOption
                                        ? Colors.green
                                        : isSelected
                                            ? Colors.red
                                            : Colors.grey,
                                    size: 20,
                                  ),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: Text(
                                      option.optionText,
                                      style: TextStyle(
                                        fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            );
                          }),

                        // Explanation
                        if (question?.explanation != null) ...[
                          const SizedBox(height: 12),
                          Container(
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: Colors.blue.withOpacity(0.05),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text(
                                  'Explanation',
                                  style: TextStyle(
                                    fontWeight: FontWeight.bold,
                                    color: Colors.blue,
                                  ),
                                ),
                                const SizedBox(height: 4),
                                Text(question!.explanation!),
                              ],
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                );
              },
            ),
    );
  }
}
