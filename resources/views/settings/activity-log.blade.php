{{-- resources/views/settings/activity-log.blade.php --}}
@extends('layouts.app')
@section('title','Activity Log')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Activity Log</h4>
        <p class="text-muted mb-0 small">All user actions in this business</p>
    </div>
</div>
<div class="card-dark">
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0">
            <thead>
                <tr class="text-muted small">
                    <th>Time</th><th>User</th><th>Action</th><th>Description</th><th>IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td class="text-muted small text-nowrap">{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y H:i') }}</td>
                    <td>{{ $log->user_name ?? 'System' }}</td>
                    <td>
                        <span class="badge" style="background:#1E3A5F;color:#93C5FD">{{ $log->action }}</span>
                    </td>
                    <td class="small">{{ $log->description }}</td>
                    <td class="font-monospace small text-muted">{{ $log->ip_address }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No activity logged</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $logs->links() }}</div>
</div>
@endsection
