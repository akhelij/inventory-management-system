@extends('layouts.tabler')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    Edit Progress Item
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('progress-items.index') }}" class="btn btn-outline-secondary d-none d-sm-inline-block">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrow-left" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M5 12l14 0"></path>
                            <path d="M5 12l6 6"></path>
                            <path d="M5 12l6 -6"></path>
                        </svg>
                        Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-12">
                <form action="{{ route('progress-items.update', $progressItem) }}" method="POST" class="card">
                    @csrf
                    @method('PUT')
                    <div class="card-header">
                        <h4 class="card-title">Progress Item Information</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label required">Title</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title', $progressItem->title) }}" placeholder="Progress item title">
                            @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="4" placeholder="Progress item description">{{ old('description', $progressItem->description) }}</textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Price (€)</label>
                                <input type="number" step="0.01" min="0" class="form-control @error('price') is-invalid @enderror" name="price" value="{{ old('price', $progressItem->price) }}" placeholder="0.00">
                                @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Amount Paid (€)</label>
                                <input type="number" step="0.01" min="0" class="form-control @error('amount_paid') is-invalid @enderror" name="amount_paid" value="{{ old('amount_paid', $progressItem->amount_paid) }}" placeholder="0.00">
                                @error('amount_paid')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Status</label>
                                <select class="form-select @error('status') is-invalid @enderror" name="status">
                                    <option value="not_started" {{ old('status', $progressItem->status) == 'not_started' ? 'selected' : '' }}>Not Started</option>
                                    <option value="in_progress" {{ old('status', $progressItem->status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="completed" {{ old('status', $progressItem->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                                @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-check form-switch mt-4">
                                    <input type="hidden" name="is_visible" value="0">
                                    <input class="form-check-input" type="checkbox" name="is_visible" value="1" {{ old('is_visible', $progressItem->is_visible) ? 'checked' : '' }}>
                                    <span class="form-check-label">Visible</span>
                                </label>
                                <small class="form-hint">Progress item will be automatically hidden when paid and completed</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="alert alert-info mb-0">
                                    <div class="d-flex">
                                        <div>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                <path d="M12 9h.01"></path>
                                                <path d="M11 12h1v4h1"></path>
                                                <path d="M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="alert-title">Payment Status</h4>
                                            <div class="text-muted">
                                                @if($progressItem->payment_status == 'unpaid')
                                                Not paid
                                                @elseif($progressItem->payment_status == 'partially_paid')
                                                Partially paid ({{ number_format($progressItem->amount_paid / $progressItem->price * 100, 0) }}%)
                                                @else
                                                Fully paid
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert {{ $progressItem->remaining_amount > 0 ? 'alert-warning' : 'alert-success' }} mb-0">
                                    <div class="d-flex">
                                        <div>
                                            @if($progressItem->remaining_amount > 0)
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                <path d="M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z"></path>
                                                <path d="M12 8v4"></path>
                                                <path d="M12 16h.01"></path>
                                            </svg>
                                            @else
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                <path d="M5 12l5 5l10 -10"></path>
                                            </svg>
                                            @endif
                                        </div>
                                        <div>
                                            <h4 class="alert-title">Remaining Amount</h4>
                                            <div class="text-muted">
                                                {{ number_format($progressItem->remaining_amount, 2) }} € out of {{ number_format($progressItem->price, 2) }} €
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary">Update Progress Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 