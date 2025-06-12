@extends('layouts.admin')

@section('title', 'Edit Activity')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Edit Activity</h1>
        <a href="{{ route('admin.activities.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Activities
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.activities.update', $activity) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label">Activity Name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                        value="{{ old('name', $activity->name) }}" required>
                    @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="intensity_level" class="form-label">Intensity Level</label>
                    <select class="form-select @error('intensity_level') is-invalid @enderror" id="intensity_level"
                        name="intensity_level" required>
                        <option value="">Select Intensity Level</option>
                        <option value="high"
                            {{ old('intensity_level', $activity->intensity_level) == 'high' ? 'selected' : '' }}>High
                        </option>
                        <option value="moderate"
                            {{ old('intensity_level', $activity->intensity_level) == 'moderate' ? 'selected' : '' }}>
                            Moderate</option>
                        <option value="low"
                            {{ old('intensity_level', $activity->intensity_level) == 'low' ? 'selected' : '' }}>Low
                        </option>
                    </select>
                    @error('intensity_level')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description"
                        name="description" rows="3">{{ old('description', $activity->description) }}</textarea>
                    @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input @error('active') is-invalid @enderror" id="active"
                        name="active" value="1" {{ old('active', $activity->active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="active">Active</label>
                    @error('active')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update Activity
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection