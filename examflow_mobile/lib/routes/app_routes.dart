import 'package:flutter/material.dart';
import '../../features/authentication/screens/login_screen.dart';
import '../../features/authentication/screens/register_screen.dart';
import '../../features/authentication/screens/forgot_password_screen.dart';
import '../../features/dashboard/screens/home_screen.dart';
import '../../features/practice/screens/practice_screen.dart';
import '../../features/practice/screens/exam_selection_screen.dart';
import '../../features/mock_test/screens/mock_test_list_screen.dart';
import '../../features/mock_test/screens/mock_test_player_screen.dart';
import '../../features/mock_test/screens/instructions_screen.dart';
import '../../features/results/screens/result_screen.dart';
import '../../features/results/screens/analytics_screen.dart';
import '../../features/results/screens/review_answers_screen.dart';
import '../../features/profile/screens/profile_screen.dart';
import '../../features/contributor/screens/create_question_screen.dart';
import '../../features/contributor/screens/my_contributions_screen.dart';
import '../../features/contributor/screens/create_test_draft_screen.dart';
import '../../features/contributor/screens/teacher_detail_screen.dart';
import '../../features/reviewer/screens/review_queue_screen.dart';
import '../../features/leaderboard/screens/leaderboard_screen.dart';
import '../../features/notifications/screens/notifications_screen.dart';

class AppRoutes {
  static const String login = '/login';
  static const String register = '/register';
  static const String forgotPassword = '/forgot-password';
  static const String home = '/home';
  static const String practice = '/practice';
  static const String examSelection = '/exam-selection';
  static const String mockTestList = '/mock-tests';
  static const String mockTestPlayer = '/mock-test-player';
  static const String instructions = '/instructions';
  static const String result = '/result';
  static const String analytics = '/analytics';
  static const String reviewAnswers = '/review-answers';
  static const String profile = '/profile';
  static const String createQuestion = '/create-question';
  static const String myContributions = '/my-contributions';
  static const String createTestDraft = '/create-test-draft';
  static const String teacherDetail = '/teacher-detail';
  static const String reviewQueue = '/review-queue';
  static const String questionDetail = '/question-detail';
  static const String leaderboard = '/leaderboard';
  static const String notifications = '/notifications';

  static Map<String, WidgetBuilder> get routes {
    return {
      login: (context) => const LoginScreen(),
      register: (context) => const RegisterScreen(),
      forgotPassword: (context) => const ForgotPasswordScreen(),
      home: (context) => const HomeScreen(),
      practice: (context) => const PracticeScreen(),
      examSelection: (context) => const ExamSelectionScreen(),
      mockTestList: (context) => const MockTestListScreen(),
      mockTestPlayer: (context) => const MockTestPlayerScreen(),
      instructions: (context) {
        final args = ModalRoute.of(context)?.settings.arguments;
        final mockTestId = args is Map<String, dynamic> ? args['mock_test_id'] as int : (args as int? ?? 0);
        return InstructionsScreen(mockTestId: mockTestId);
      },
      result: (context) => const ResultScreen(),
      analytics: (context) => const AnalyticsScreen(),
      reviewAnswers: (context) {
        final args = ModalRoute.of(context)?.settings.arguments as Map<String, dynamic>? ?? {};
        return ReviewAnswersScreen(
          answers: args['answers'] ?? [],
          questions: args['questions'] ?? {},
        );
      },
      profile: (context) => const ProfileScreen(),
      createQuestion: (context) => const CreateQuestionScreen(),
      myContributions: (context) => const MyContributionsScreen(),
      createTestDraft: (context) => const CreateTestDraftScreen(),
      teacherDetail: (context) => const TeacherDetailScreen(),
      reviewQueue: (context) => const ReviewQueueScreen(),
      leaderboard: (context) => const LeaderboardScreen(),
      notifications: (context) => const NotificationsScreen(),
    };
  }
}
