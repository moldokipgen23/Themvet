class ApiConstants {
  static const String baseUrl = String.fromEnvironment('API_BASE_URL', defaultValue: 'https://themvet.ehlom.com/api');
  
  // Auth
  static const String register = '/auth/register';
  static const String login = '/auth/login';
  static const String logout = '/auth/logout';
  static const String profile = '/auth/profile';
  static const String changePassword = '/auth/change-password';
  static const String forgotPassword = '/auth/forgot-password';
  static const String resetPassword = '/auth/reset-password';
  static const String verifyOtp = '/auth/verify-otp';
  static const String googleAuth = '/auth/google';
  static const String googleCallback = '/auth/google/callback';
  static const String sendVerificationEmail = '/auth/send-verification-email';
  static const String verifyEmail = '/auth/verify-email';
  
  // Settings
  static const String settings = '/settings';
  
  // Exams
  static const String exams = '/exams';
  static const String examPatterns = '/exam-patterns';
  
  // Questions
  static const String questionsPractice = '/questions/practice';
  
  // Mock Tests
  static const String mockTests = '/mock-tests';
  
  // Attempts
  static const String attempts = '/attempts';
  static const String resultsSummary = '/results/summary';
  static const String resultsProgress = '/results/progress';

  // Leaderboard
  static const String leaderboard = '/leaderboard';
  static const String leaderboardMyStats = '/leaderboard/my-stats';

  // Notifications
  static const String notifications = '/notifications';

  // Device Token
  static const String deviceToken = '/device-token';

  // Teacher (Content Creation)
  static const String teacherQuestions = '/contributor/questions';
  static const String teacherStats = '/contributor/stats';
  static const String teacherMockTests = '/contributor/mock-tests';

  // Reviewer
  static const String reviewerQueue = '/reviewer/queue';
  static const String reviewerAssignments = '/reviewer/my-assignments';
}
