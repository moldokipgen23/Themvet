import 'package:flutter/material.dart';
import '../../../core/services/api_service.dart';
import '../../../core/constants/api_constants.dart';
import '../../../core/models/models.dart';
import '../../../routes/app_routes.dart';
import '../../practice/screens/exam_selection_screen.dart';
import '../../mock_test/screens/mock_test_list_screen.dart';
import '../../profile/screens/profile_screen.dart';
import '../../leaderboard/screens/leaderboard_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _currentIndex = 0;
  User? _user;
  List<Exam> _exams = [];
  bool _isLoading = true;
  bool _hasError = false;
  String _errorMessage = '';

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    try {
      final profileResponse = await ApiService.get(ApiConstants.profile);
      final examsResponse = await ApiService.get(ApiConstants.exams);

      if (mounted) {
        setState(() {
          _user = User.fromJson(profileResponse['data']['user']);
          _exams = (examsResponse['data']['exams'] as List)
              .map((e) => Exam.fromJson(e))
              .toList();
          _isLoading = false;
          _hasError = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isLoading = false;
          _hasError = true;
          _errorMessage = e.toString();
        });
      }
    }
  }

  Future<void> _logout() async {
    try {
      await ApiService.post(ApiConstants.logout);
    } catch (e) {}
    await ApiService.clearToken();
    if (mounted) {
      Navigator.pushReplacementNamed(context, AppRoutes.login);
    }
  }

  bool get _hasExtraRoles => _user?.isTeacher == true || _user?.isReviewer == true;

  List<Widget> get _tabs {
    final tabs = <Widget>[
      _buildHomeTab(),
      _buildPracticeTab(),
      _buildTestsTab(),
      _buildLeaderboardTab(),
      _buildProfileTab(),
    ];
    return tabs;
  }

  List<BottomNavigationBarItem> get _navItems {
    final items = <BottomNavigationBarItem>[
      const BottomNavigationBarItem(icon: Icon(Icons.home), label: 'Home'),
      const BottomNavigationBarItem(icon: Icon(Icons.school), label: 'Practice'),
      const BottomNavigationBarItem(icon: Icon(Icons.quiz), label: 'Tests'),
      const BottomNavigationBarItem(icon: Icon(Icons.leaderboard), label: 'Leaderboard'),
      const BottomNavigationBarItem(icon: Icon(Icons.person), label: 'Profile'),
    ];
    return items;
  }

  void _showMoreActions() {
    showModalBottomSheet(
      context: context,
      builder: (ctx) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Padding(
              padding: EdgeInsets.all(16),
              child: Text('Teacher & Reviewer', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
            ),
            if (_user?.isTeacher == true)
              ListTile(
                leading: const Icon(Icons.dashboard, color: Colors.teal),
                title: const Text('Teacher Dashboard'),
                onTap: () {
                  Navigator.pop(ctx);
                  Navigator.pushNamed(context, AppRoutes.teacherDetail);
                },
              ),
            if (_user?.isTeacher == true)
              ListTile(
                leading: const Icon(Icons.add_circle, color: Colors.green),
                title: const Text('Create Question'),
                onTap: () {
                  Navigator.pop(ctx);
                  Navigator.pushNamed(context, AppRoutes.createQuestion);
                },
              ),
            if (_user?.isTeacher == true)
              ListTile(
                leading: const Icon(Icons.quiz, color: Colors.green),
                title: const Text('My Contributions'),
                onTap: () {
                  Navigator.pop(ctx);
                  Navigator.pushNamed(context, AppRoutes.myContributions);
                },
              ),
            if (_user?.isReviewer == true)
              ListTile(
                leading: const Icon(Icons.rate_review, color: Colors.orange),
                title: const Text('Review Queue'),
                onTap: () {
                  Navigator.pop(ctx);
                  Navigator.pushNamed(context, AppRoutes.reviewQueue);
                },
              ),
            const SizedBox(height: 8),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }

    if (_hasError) {
      return Scaffold(
        appBar: AppBar(title: const Text('ThemVet')),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.error_outline, size: 64, color: Colors.red[300]),
              const SizedBox(height: 16),
              Text(
                'Something went wrong',
                style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Colors.grey[600]),
              ),
              const SizedBox(height: 8),
              Text(
                _errorMessage.isNotEmpty ? _errorMessage : 'Please check your connection and try again.',
                textAlign: TextAlign.center,
                style: TextStyle(color: Colors.grey[500]),
              ),
              const SizedBox(height: 24),
              ElevatedButton.icon(
                onPressed: () {
                  setState(() { _isLoading = true; _hasError = false; });
                  _loadData();
                },
                icon: const Icon(Icons.refresh),
                label: const Text('Retry'),
              ),
              const SizedBox(height: 12),
              TextButton(
                onPressed: _logout,
                child: const Text('Logout'),
              ),
            ],
          ),
        ),
      );
    }

    if (_user?.isSystemUser == true) {
      return Scaffold(
        appBar: AppBar(title: const Text('ThemVet')),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.admin_panel_settings, size: 64, color: Colors.grey[400]),
              const SizedBox(height: 16),
              Text(
                'Admin Access',
                style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: Colors.grey[600]),
              ),
              const SizedBox(height: 8),
              Text(
                'Please use the admin web panel\nfor system management.',
                textAlign: TextAlign.center,
                style: TextStyle(color: Colors.grey[500], fontSize: 16),
              ),
              const SizedBox(height: 24),
              ElevatedButton.icon(
                onPressed: _logout,
                icon: const Icon(Icons.logout),
                label: const Text('Logout'),
              ),
            ],
          ),
        ),
      );
    }

    return Scaffold(
      body: _tabs[_currentIndex],
      floatingActionButton: _hasExtraRoles && _currentIndex == 0
          ? FloatingActionButton(
              onPressed: _showMoreActions,
              child: const Icon(Icons.add),
            )
          : null,
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _currentIndex,
        onTap: (index) => setState(() => _currentIndex = index),
        type: BottomNavigationBarType.fixed,
        selectedItemColor: Theme.of(context).colorScheme.primary,
        unselectedItemColor: Colors.grey,
        items: _navItems,
      ),
    );
  }

  Widget _buildHomeTab() {
    return RefreshIndicator(
      onRefresh: _loadData,
      child: CustomScrollView(
      slivers: [
        SliverAppBar(
          expandedHeight: 140,
          pinned: true,
          flexibleSpace: FlexibleSpaceBar(
            title: const Text('ThemVet'),
            background: Container(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: [
                    Theme.of(context).colorScheme.primary,
                    Theme.of(context).colorScheme.primary.withOpacity(0.7),
                  ],
                ),
              ),
              child: Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text(
                      'Welcome, ${_user?.name ?? 'Student'}!',
                      style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(height: 4),
                    // Show all roles
                    Wrap(
                      spacing: 6,
                      children: _user?.roles?.map((role) {
                        final colors = {
                          'student': Colors.blue,
                          'contributor': Colors.green,
                          'teacher': Colors.green,
                          'reviewer': Colors.orange,
                          'lead_reviewer': Colors.purple,
                          'admin': Colors.red,
                          'moderator': Colors.amber,
                        };
                        return Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                          decoration: BoxDecoration(
                            color: (colors[role.name] ?? Colors.grey).withOpacity(0.3),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Text(
                            role.name.toUpperCase(),
                            style: const TextStyle(color: Colors.white, fontSize: 10),
                          ),
                        );
                      }).toList() ?? [],
                    ),
                  ],
                ),
              ),
            ),
          ),
          actions: [
            IconButton(icon: const Icon(Icons.logout), onPressed: _logout),
          ],
        ),
        SliverPadding(
          padding: const EdgeInsets.all(16),
          sliver: SliverList(
            delegate: SliverChildListDelegate([
              // Role Banner
              if (_user?.isTeacher == true || _user?.isReviewer == true)
                _buildRoleBanner(),
              const SizedBox(height: 16),

              // Quick Actions
              const Text('Quick Actions', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
              const SizedBox(height: 12),
              _buildQuickActions(),
              const SizedBox(height: 24),

              // Exams Section
              const Text('Available Exams', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
              const SizedBox(height: 12),
              ..._exams.map((exam) => _buildExamCard(exam)),
            ]),
          ),
        ),
      ],
    ),
    );
  }

  Widget _buildRoleBanner() {
    final roles = <String>[];
    if (_user?.isTeacher == true) roles.add('Creator');
    if (_user?.isReviewer == true) roles.add('Reviewer');

    return Card(
      color: Theme.of(context).colorScheme.primary.withOpacity(0.1),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          children: [
            Icon(Icons.star, color: Theme.of(context).colorScheme.primary),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Extra Powers: ${roles.join(" + ")}',
                    style: const TextStyle(fontWeight: FontWeight.bold),
                  ),
                  Text(
                    'You can ${_user?.isTeacher == true ? "create questions" : ""}${_user?.isTeacher == true && _user?.isReviewer == true ? " and " : ""}${_user?.isReviewer == true ? "review questions" : ""}!',
                    style: TextStyle(color: Colors.grey[600], fontSize: 12),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildQuickActions() {
    return Wrap(
      spacing: 12,
      runSpacing: 12,
      children: [
        _buildActionCard(
          icon: Icons.play_circle,
          title: 'Start Practice',
          onTap: () => setState(() => _currentIndex = 1),
        ),
        _buildActionCard(
          icon: Icons.timer,
          title: 'Take Mock Test',
          onTap: () => setState(() => _currentIndex = 2),
        ),
        if (_user?.isTeacher == true)
          _buildActionCard(
            icon: Icons.dashboard,
            title: 'Teacher Dashboard',
            color: Colors.teal,
            onTap: () => Navigator.pushNamed(context, AppRoutes.teacherDetail),
          ),
        if (_user?.isTeacher == true)
          _buildActionCard(
            icon: Icons.add_circle,
            title: 'Create Question',
            color: Colors.green,
            onTap: () => Navigator.pushNamed(context, AppRoutes.createQuestion),
          ),
        if (_user?.isReviewer == true)
          _buildActionCard(
            icon: Icons.rate_review,
            title: 'Review Queue',
            color: Colors.orange,
            onTap: () => Navigator.pushNamed(context, AppRoutes.reviewQueue),
          ),
        _buildActionCard(
          icon: Icons.leaderboard,
          title: 'Leaderboard',
          color: Colors.purple,
          onTap: () => Navigator.pushNamed(context, AppRoutes.leaderboard),
        ),
        _buildActionCard(
          icon: Icons.notifications,
          title: 'Notifications',
          color: Colors.teal,
          onTap: () => Navigator.pushNamed(context, AppRoutes.notifications),
        ),
      ],
    );
  }

  Widget _buildActionCard({
    required IconData icon,
    required String title,
    required VoidCallback onTap,
    Color? color,
  }) {
    return SizedBox(
      width: (MediaQuery.of(context).size.width - 44) / 2,
      child: Card(
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(12),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              children: [
                Icon(icon, size: 32, color: color ?? Theme.of(context).colorScheme.primary),
                const SizedBox(height: 8),
                Text(title, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildExamCard(Exam exam) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor: Theme.of(context).colorScheme.primary.withOpacity(0.1),
          child: Text(
            exam.name.substring(0, 2),
            style: TextStyle(color: Theme.of(context).colorScheme.primary, fontWeight: FontWeight.bold),
          ),
        ),
        title: Text(exam.name),
        subtitle: Text('${exam.subjects?.length ?? 0} subjects'),
        trailing: const Icon(Icons.chevron_right),
        onTap: () => setState(() => _currentIndex = 1),
      ),
    );
  }

  Widget _buildPracticeTab() {
    return Navigator(
      onGenerateRoute: (settings) => MaterialPageRoute(
        builder: (context) => const ExamSelectionScreen(),
      ),
    );
  }

  Widget _buildTestsTab() {
    return Navigator(
      onGenerateRoute: (settings) => MaterialPageRoute(
        builder: (context) => const MockTestListScreen(),
      ),
    );
  }

  Widget _buildLeaderboardTab() {
    return Navigator(
      onGenerateRoute: (settings) => MaterialPageRoute(
        builder: (context) => const LeaderboardScreen(),
      ),
    );
  }

  Widget _buildProfileTab() {
    return Navigator(
      onGenerateRoute: (settings) => MaterialPageRoute(
        builder: (context) => const ProfileScreen(),
      ),
    );
  }
}
