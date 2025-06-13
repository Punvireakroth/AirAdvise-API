@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="container">
    <h1 class="mb-4">Admin Dashboard</h1>

    <div class="row">
        <div class="col-md-4">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase">Total Users</h6>
                            <h2 class="mb-0">{{ \App\Models\User::count() }}</h2>
                        </div>
                        <i class="fas fa-users fa-3x"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('admin.users.index') }}">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase">Pending Feedback</h6>
                            <h2 class="mb-0">{{ \App\Models\Feedback::where('status', 'pending')->count() }}</h2>
                        </div>
                        <i class="fas fa-comments fa-3x"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link"
                        href="{{ route('admin.feedback.index', ['status' => 'pending']) }}">View Pending</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase">Total Feedback</h6>
                            <h2 class="mb-0">{{ \App\Models\Feedback::count() }}</h2>
                        </div>
                        <i class="fas fa-comment-dots fa-3x"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('admin.feedback.index') }}">View All</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Recent Users
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(\App\Models\User::latest()->take(5)->get() as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->created_at->diffForHumans() }}</td>
                            <td>
                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Feedback</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>User</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(\App\Models\Feedback::with('user')->latest()->take(5)->get() as $feedback)
                                <tr>
                                    <td>{{ Str::limit($feedback->subject, 30) }}</td>
                                    <td>{{ $feedback->user->name ?? 'Unknown' }}</td>
                                    <td>
                                        @if($feedback->status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                        @elseif($feedback->status === 'responded')
                                        <span class="badge bg-info">Responded</span>
                                        @elseif($feedback->status === 'resolved')
                                        <span class="badge bg-success">Resolved</span>
                                        @elseif($feedback->status === 'archived')
                                        <span class="badge bg-secondary">Archived</span>
                                        @endif
                                    </td>
                                    <td>{{ $feedback->created_at->diffForHumans() }}</td>
                                    <td>
                                        <a href="{{ route('admin.feedback.show', $feedback) }}"
                                            class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection