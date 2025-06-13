@extends('layouts.admin')

@section('title', 'Feedback Details')

@section('styles')
<style>
    .feedback-container {
        background-color: #f8f9fa;
        border-radius: 0.5rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .response-container {
        background-color: #e9f7ef;
        border-radius: 0.5rem;
        padding: 1.5rem;
        margin-bottom: 1rem;
        margin-left: 2rem;
    }

    .status-form {
        display: inline;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Feedback Detail</h1>
        <a href="{{ route('admin.feedback.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> Back to Feedback
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Status:
                @if($feedback->status === 'pending')
                <span class="badge bg-warning">Pending</span>
                @elseif($feedback->status === 'responded')
                <span class="badge bg-info">Responded</span>
                @elseif($feedback->status === 'resolved')
                <span class="badge bg-success">Resolved</span>
                @elseif($feedback->status === 'archived')
                <span class="badge bg-secondary">Archived</span>
                @endif
            </h5>
            <div>
                <form action="{{ route('admin.feedback.update-status', $feedback) }}" method="POST" class="d-inline">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="resolved">
                    <button type="submit" class="btn btn-sm btn-success">
                        <i class="fas fa-check me-1"></i> Mark as Resolved
                    </button>
                </form>

                <form action="{{ route('admin.feedback.update-status', $feedback) }}" method="POST" class="d-inline">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="archived">
                    <button type="submit" class="btn btn-sm btn-secondary">
                        <i class="fas fa-archive me-1"></i> Archive
                    </button>
                </form>

                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                    data-bs-target="#deleteModal">
                    <i class="fas fa-trash me-1"></i> Delete
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="feedback-container">
                <div class="d-flex justify-content-between">
                    <h5>{{ $feedback->subject }}</h5>
                    <span class="text-muted">{{ $feedback->created_at->format('M d, Y H:i') }}</span>
                </div>
                <p><strong>From:</strong> {{ $feedback->user->name }} ({{ $feedback->user->email }})</p>
                <div class="mt-3">
                    {{ $feedback->message }}
                </div>
            </div>

            @if($feedback->responses->count() > 0)
            <h5 class="mt-4 mb-3">Responses</h5>

            @foreach($feedback->responses as $response)
            <div class="response-container">
                <div class="d-flex justify-content-between">
                    <h6>Response from {{ $response->admin->name }} (Admin)</h6>
                    <span class="text-muted">{{ $response->created_at->format('M d, Y H:i') }}</span>
                </div>
                <div class="mt-3">
                    {{ $response->message }}
                </div>
            </div>
            @endforeach
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Reply to Feedback</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.feedback.respond', $feedback) }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="response" class="form-label">Response Message</label>
                    <textarea class="form-control @error('response') is-invalid @enderror" id="response" name="response"
                        rows="5" required>{{ old('response') }}</textarea>
                    @error('response')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-reply me-1"></i> Send Response
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this feedback? This action cannot be undone and will delete all
                responses as well.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.feedback.destroy', $feedback) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection