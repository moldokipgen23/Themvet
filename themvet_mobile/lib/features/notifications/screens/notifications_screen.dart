import 'package:flutter/material.dart';
import '../../../core/services/api_service.dart';
import '../../../core/constants/api_constants.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  List _notifications = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadNotifications();
  }

  Future<void> _loadNotifications() async {
    try {
      final response = await ApiService.get(ApiConstants.notifications);
      if (mounted) {
        setState(() {
          _notifications = response['data']['notifications'] ?? [];
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  Future<void> _markAsRead(int id) async {
    try {
      await ApiService.post('${ApiConstants.notifications}/$id/read');
      _loadNotifications();
    } catch (_) {}
  }

  Future<void> _markAllRead() async {
    try {
      await ApiService.post('${ApiConstants.notifications}/read-all');
      _loadNotifications();
    } catch (_) {}
  }

  IconData _getNotificationIcon(String type) {
    switch (type) {
      case 'badge_earned':
        return Icons.emoji_events;
      case 'streak_milestone':
        return Icons.local_fire_department;
      case 'test_result':
        return Icons.quiz;
      default:
        return Icons.notifications;
    }
  }

  Color _getNotificationColor(String type) {
    switch (type) {
      case 'badge_earned':
        return Colors.amber;
      case 'streak_milestone':
        return Colors.orange;
      case 'test_result':
        return Theme.of(context).colorScheme.primary;
      default:
        return Colors.grey;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Notifications'),
        actions: [
          if (_notifications.any((n) => n['read_at'] == null))
            TextButton(
              onPressed: _markAllRead,
              child: const Text('Mark All Read'),
            ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _notifications.isEmpty
              ? const Center(child: Text('No notifications yet'))
              : ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: _notifications.length,
                  itemBuilder: (context, index) {
                    final notification = _notifications[index];
                    final isUnread = notification['read_at'] == null;
                    return Card(
                      color: isUnread ? Theme.of(context).colorScheme.primary.withOpacity(0.05) : null,
                      margin: const EdgeInsets.only(bottom: 8),
                      child: ListTile(
                        leading: CircleAvatar(
                          backgroundColor: _getNotificationColor(notification['type'] ?? '').withOpacity(0.2),
                          child: Icon(
                            _getNotificationIcon(notification['type'] ?? ''),
                            color: _getNotificationColor(notification['type'] ?? ''),
                          ),
                        ),
                        title: Text(
                          notification['message'] ?? '',
                          style: TextStyle(
                            fontWeight: isUnread ? FontWeight.bold : FontWeight.normal,
                          ),
                        ),
                        subtitle: Text(
                          _formatDate(notification['created_at']),
                          style: const TextStyle(fontSize: 12),
                        ),
                        trailing: isUnread
                            ? Container(
                                width: 10,
                                height: 10,
                                decoration: BoxDecoration(
                                  color: Theme.of(context).colorScheme.primary,
                                  shape: BoxShape.circle,
                                ),
                              )
                            : null,
                        onTap: isUnread ? () => _markAsRead(notification['id']) : null,
                      ),
                    );
                  },
                ),
    );
  }

  String _formatDate(String? dateStr) {
    if (dateStr == null) return '';
    final date = DateTime.tryParse(dateStr);
    if (date == null) return '';
    final now = DateTime.now();
    final diff = now.difference(date);
    if (diff.inMinutes < 60) return '${diff.inMinutes}m ago';
    if (diff.inHours < 24) return '${diff.inHours}h ago';
    if (diff.inDays < 7) return '${diff.inDays}d ago';
    return '${date.day}/${date.month}/${date.year}';
  }
}
