import 'package:flutter/material.dart';
import '../../../core/services/api_service.dart';
import '../../../core/constants/api_constants.dart';
import '../../../core/models/models.dart';
import '../../../routes/app_routes.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  User? _user;
  Map? _gamificationStats;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    try {
      final profileResponse = await ApiService.get(ApiConstants.profile);
      final statsResponse = await ApiService.get(ApiConstants.resultsSummary);
      if (mounted) {
        setState(() {
          _user = User.fromJson(profileResponse['data']['user']);
          _gamificationStats = statsResponse['data']['gamification'];
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isLoading = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error loading data: $e')),
        );
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

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('Profile'),
        actions: [
          IconButton(icon: const Icon(Icons.logout), onPressed: _logout),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _loadData,
        child: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            _buildProfileHeader(),
            const SizedBox(height: 16),
            if (_gamificationStats != null) _buildStreakCard(),
            const SizedBox(height: 16),
            if (_gamificationStats != null) _buildStatsCard(),
            const SizedBox(height: 16),
            if (_gamificationStats != null) _buildBadgesSection(),
            const SizedBox(height: 16),
            _buildMenuSection(),
            const SizedBox(height: 32),
          ],
        ),
      ),
      ),
    );
  }

  Widget _buildProfileHeader() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          children: [
            CircleAvatar(
              radius: 50,
              backgroundColor: Theme.of(context).colorScheme.primary.withOpacity(0.1),
              child: Text(
                _user != null && _user!.name.isNotEmpty
                    ? _user!.name.substring(0, 1).toUpperCase()
                    : 'U',
                style: TextStyle(fontSize: 40, color: Theme.of(context).colorScheme.primary),
              ),
            ),
            const SizedBox(height: 16),
            Text(_user?.name ?? 'User', style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold)),
            const SizedBox(height: 8),
            Text(_user?.email ?? '', style: TextStyle(color: Colors.grey[600])),
            const SizedBox(height: 12),
            Wrap(
              spacing: 8,
              children: _user?.roles?.map((role) {
                final colors = {
                  'admin': Colors.red,
                  'teacher': Colors.green,
                  'student': Colors.blue,
                };
                final color = colors[role.name] ?? Colors.grey;
                return Chip(
                  label: Text(role.name.toUpperCase(), style: const TextStyle(fontSize: 10)),
                  backgroundColor: color.withOpacity(0.1),
                  labelStyle: TextStyle(color: color),
                );
              }).toList() ?? [],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStreakCard() {
    final streak = _gamificationStats!['streak'] ?? {};
    final currentStreak = streak['current_streak'] ?? 0;
    final longestStreak = streak['longest_streak'] ?? 0;

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceAround,
          children: [
            Column(
              children: [
                Icon(Icons.local_fire_department, color: currentStreak > 0 ? Colors.orange : Colors.grey, size: 32),
                const SizedBox(height: 4),
                Text('$currentStreak', style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
                const Text('Current', style: TextStyle(fontSize: 11, color: Colors.grey)),
              ],
            ),
            Container(height: 40, width: 1, color: Colors.grey[300]),
            Column(
              children: [
                Icon(Icons.local_fire_department, color: Colors.orange, size: 32),
                const SizedBox(height: 4),
                Text('$longestStreak', style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
                const Text('Longest', style: TextStyle(fontSize: 11, color: Colors.grey)),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatsCard() {
    final stats = _gamificationStats!['stats'] ?? {};
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceAround,
          children: [
            _buildStatItem('Tests', '${stats['total_tests_taken'] ?? 0}'),
            _buildStatDivider(),
            _buildStatItem('Accuracy', '${stats['average_accuracy']?.toStringAsFixed(1) ?? '0'}%'),
            _buildStatDivider(),
            _buildStatItem('Points', '${stats['total_points'] ?? 0}'),
          ],
        ),
      ),
    );
  }

  Widget _buildBadgesSection() {
    final badges = _gamificationStats!['badges'] as List? ?? [];
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Row(
              children: [
                Icon(Icons.emoji_events, color: Colors.amber),
                SizedBox(width: 8),
                Text('Badges & Achievements', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
              ],
            ),
            const SizedBox(height: 16),
            if (badges.isEmpty)
              const Center(
                child: Padding(
                  padding: EdgeInsets.all(16),
                  child: Text('Complete tests to earn badges!', style: TextStyle(color: Colors.grey)),
                ),
              )
            else
              Wrap(
                spacing: 12,
                runSpacing: 12,
                children: badges.map((badge) {
                  Color color;
                  try {
                    color = badge['color'] != null
                        ? Color(int.parse(badge['color'].replaceFirst('#', '0xFF')))
                        : Colors.amber;
                  } catch (_) {
                    color = Colors.amber;
                  }
                  return Tooltip(
                    message: badge['description'] ?? '',
                    child: Container(
                      width: 70,
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: color.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: color.withOpacity(0.3)),
                      ),
                      child: Column(
                        children: [
                          Icon(Icons.emoji_events, color: color, size: 28),
                          const SizedBox(height: 4),
                          Text(
                            badge['name'] ?? '',
                            textAlign: TextAlign.center,
                            style: TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: color),
                          ),
                        ],
                      ),
                    ),
                  );
                }).toList(),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildMenuSection() {
    return Card(
      child: Column(
        children: [
          _buildMenuItem(Icons.edit, 'Edit Profile', () {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(content: Text('Edit Profile coming soon!')),
            );
          }),
          const Divider(height: 1),
          _buildMenuItem(Icons.lock, 'Change Password', () {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(content: Text('Change Password coming soon!')),
            );
          }),
          const Divider(height: 1),
          _buildMenuItem(Icons.history, 'Test History', () {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(content: Text('Test History coming soon!')),
            );
          }),
          const Divider(height: 1),
          _buildMenuItem(Icons.leaderboard, 'Leaderboard', () {
            Navigator.pushNamed(context, AppRoutes.leaderboard);
          }),
          const Divider(height: 1),
          _buildMenuItem(Icons.notifications, 'Notifications', () {
            Navigator.pushNamed(context, AppRoutes.notifications);
          }),
          const Divider(height: 1),
          _buildMenuItem(Icons.info, 'About', () {
            showAboutDialog(
              context: context,
              applicationName: 'ThemVet',
              applicationVersion: '1.0.0',
              applicationIcon: Icon(Icons.school, size: 48, color: Theme.of(context).colorScheme.primary),
              children: const [Text('ThemVet is a community-powered exam preparation platform.')],
            );
          }),
        ],
      ),
    );
  }

  Widget _buildStatItem(String label, String value) {
    return Column(
      children: [
        Text(value, style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
        const SizedBox(height: 4),
        Text(label, style: TextStyle(color: Colors.grey[600], fontSize: 12)),
      ],
    );
  }

  Widget _buildStatDivider() {
    return Container(height: 40, width: 1, color: Colors.grey[300]);
  }

  Widget _buildMenuItem(IconData icon, String title, VoidCallback onTap) {
    return ListTile(
      leading: Icon(icon),
      title: Text(title),
      trailing: const Icon(Icons.chevron_right),
      onTap: onTap,
    );
  }
}
