import 'package:flutter/material.dart';
import '../../../core/services/api_service.dart';
import '../../../core/constants/api_constants.dart';

class LeaderboardScreen extends StatefulWidget {
  const LeaderboardScreen({super.key});

  @override
  State<LeaderboardScreen> createState() => _LeaderboardScreenState();
}

class _LeaderboardScreenState extends State<LeaderboardScreen> {
  String _selectedPeriod = 'daily';
  List _leaderboard = [];
  Map? _userStats;
  int? _userRank;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadLeaderboard();
    _loadUserStats();
  }

  Future<void> _loadLeaderboard() async {
    try {
      final response = await ApiService.get(
        '${ApiConstants.leaderboard}?period=$_selectedPeriod',
      );
      if (mounted) {
        setState(() {
          _leaderboard = response['data']['leaderboard'] ?? [];
          _userRank = response['data']['user_rank'];
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  Future<void> _loadUserStats() async {
    try {
      final response = await ApiService.get(ApiConstants.leaderboardMyStats);
      if (mounted) {
        setState(() => _userStats = response['data']);
      }
    } catch (_) {}
  }

  void _switchPeriod(String period) {
    setState(() {
      _selectedPeriod = period;
      _isLoading = true;
    });
    _loadLeaderboard();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Leaderboard'),
        actions: [
          if (_userRank != null)
            Padding(
              padding: const EdgeInsets.only(right: 16),
              child: Center(
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                  decoration: BoxDecoration(
                    color: Theme.of(context).colorScheme.primary.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Text(
                    'Your Rank: #$_userRank',
                    style: TextStyle(
                      color: Theme.of(context).colorScheme.primary,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ),
            ),
        ],
      ),
      body: Column(
        children: [
          // Period Tabs
          Container(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                _buildPeriodTab('daily', 'Daily'),
                const SizedBox(width: 8),
                _buildPeriodTab('weekly', 'Weekly'),
                const SizedBox(width: 8),
                _buildPeriodTab('all_time', 'All Time'),
              ],
            ),
          ),
          // User Stats Card
          if (_userStats != null) _buildUserStatsCard(),
          // Leaderboard List
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _leaderboard.isEmpty
                    ? const Center(child: Text('No data yet. Complete a test to get ranked!'))
                    : ListView.builder(
                        padding: const EdgeInsets.symmetric(horizontal: 16),
                        itemCount: _leaderboard.length,
                        itemBuilder: (context, index) => _buildLeaderboardItem(index),
                      ),
          ),
        ],
      ),
    );
  }

  Widget _buildPeriodTab(String value, String label) {
    final isSelected = _selectedPeriod == value;
    return Expanded(
      child: GestureDetector(
        onTap: () => _switchPeriod(value),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 12),
          decoration: BoxDecoration(
            color: isSelected ? Theme.of(context).colorScheme.primary : Colors.grey[200],
            borderRadius: BorderRadius.circular(12),
          ),
          child: Text(
            label,
            textAlign: TextAlign.center,
            style: TextStyle(
              color: isSelected ? Colors.white : Colors.black87,
              fontWeight: FontWeight.bold,
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildUserStatsCard() {
    final stats = _userStats!;
    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceAround,
          children: [
            _buildStatChip(Icons.timer, '${stats['streak']?['current_streak'] ?? 0}', 'Day Streak'),
            _buildStatChip(Icons.emoji_events, '${stats['stats']?['total_points'] ?? 0}', 'Points'),
            _buildStatChip(Icons.quiz, '${stats['stats']?['total_tests_taken'] ?? 0}', 'Tests'),
          ],
        ),
      ),
    );
  }

  Widget _buildStatChip(IconData icon, String value, String label) {
    return Column(
      children: [
        Icon(icon, color: Theme.of(context).colorScheme.primary, size: 24),
        const SizedBox(height: 4),
        Text(value, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
        Text(label, style: TextStyle(color: Colors.grey[600], fontSize: 11)),
      ],
    );
  }

  Widget _buildLeaderboardItem(int index) {
    final entry = _leaderboard[index];
    final rank = entry['rank'] ?? index + 1;
    final isTop3 = rank <= 3;

    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor: isTop3
              ? [Colors.amber, Colors.grey[300], Colors.brown[300]][rank - 1]
              : Colors.grey[100],
          child: Text(
            '$rank',
            style: TextStyle(
              fontWeight: FontWeight.bold,
              color: isTop3 ? Colors.white : Colors.black87,
            ),
          ),
        ),
        title: Text(entry['name'] ?? 'Unknown'),
        trailing: Text(
          '${entry['score']} pts',
          style: TextStyle(
            fontWeight: FontWeight.bold,
            color: Theme.of(context).colorScheme.primary,
          ),
        ),
      ),
    );
  }
}
