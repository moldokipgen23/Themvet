@extends('layouts.admin')

@section('title', 'Notifications - ThemVet Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-bell"></i> Notifications</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sendNotificationModal">
        <i class="fas fa-paper-plane"></i> Send Notification
    </button>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-body">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Recipient</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Sent</th>
                </tr>
            </thead>
            <tbody>
                @forelse($notifications as $notification)
                <tr>
                    <td>{{ $notification->id }}</td>
                    <td>{{ $notification->title }}</td>
                    <td>{{ $notification->user->name ?? 'N/A' }}</td>
                    <td><span class="badge bg-info">{{ $notification->type }}</span></td>
                    <td>
                        @if($notification->is_read)
                            <span class="badge bg-success">Read</span>
                        @else
                            <span class="badge bg-warning">Unread</span>
                        @endif
                    </td>
                    <td>{{ $notification->created_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted">No notifications sent yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $notifications->links() }}
    </div>
</div>

<!-- Send Notification Modal -->
<div class="modal fade" id="sendNotificationModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.notifications.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Send Notification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Recipient</label>
                        <select name="user_id" class="form-select" required>
                            <option value="all">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea name="message" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            <option value="general">General</option>
                            <option value="achievement">Achievement</option>
                            <option value="announcement">Announcement</option>
                            <option value="reminder">Reminder</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
