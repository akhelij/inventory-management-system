@extends('layouts.tabler')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    Dev Progress Management
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('progress-items.create') }}" class="btn btn-primary d-none d-sm-inline-block">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M12 5l0 14" />
                            <path d="M5 12l14 0" />
                        </svg>
                        Add New Progress Item
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <div class="d-flex">
                <div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M5 12l5 5l10 -10"></path>
                    </svg>
                </div>
                <div>{{ session('success') }}</div>
            </div>
            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
        </div>
        @endif

        <!-- Invoice-style card -->
        <div class="card card-lg">
            <div class="card-header">
                <h3 class="card-title">Development Progress & Invoicing</h3>
                <div class="card-actions">
                    <span class="badge bg-purple text-white text-uppercase me-3">
                        Total Unpaid: {{ number_format($totalUnpaid, 2) }} €
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Progress Item</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Price</th>
                                <th>Paid</th>
                                <th>Remaining</th>
                                <th class="w-1"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($progressItems as $item)
                            <tr @if(!$item->is_visible) class="table-active opacity-75" @endif>
                                <td>
                                    <div class="d-flex flex-column">
                                        <div class="text-dark font-weight-medium">{{ $item->title }}</div>
                                        <div class="text-muted">{{ $item->created_at->format('M d, Y') }}</div>
                                    </div>
                                </td>
                                <td class="text-muted text-nowrap">{{ Str::limit($item->description, 60) }}</td>
                                <td>
                                    @if($item->status == 'not_started')
                                    <span class="badge bg-muted text-white text-uppercase">Not Started</span>
                                    @elseif($item->status == 'in_progress')
                                    <span class="badge bg-yellow text-white text-uppercase">In Progress</span>
                                    @else
                                    <span class="badge bg-green text-white text-uppercase">Completed</span>
                                    @endif
                                </td>
                                <td class="text-muted text-nowrap">{{ number_format($item->price, 2) }} €</td>
                                <td class="text-muted text-nowrap">
                                    <span @if($item->payment_status == 'paid') class="text-green" @endif>
                                        {{ number_format($item->amount_paid, 2) }} €
                                    </span>
                                </td>
                                <td class="text-muted text-nowrap">
                                    <span @if($item->remaining_amount > 0) class="text-red" @endif>
                                        {{ number_format($item->remaining_amount, 2) }} €
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-list flex-nowrap">
                                        @if($item->remaining_amount > 0)
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#payment-modal-{{ $item->id }}">
                                            Pay
                                        </button>
                                        @endif
                                        <a href="{{ route('progress-items.edit', $item) }}" class="btn btn-sm btn-primary">
                                            Edit
                                        </a>
                                        <form action="{{ route('progress-items.destroy', $item) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this progress item?')">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <!-- Payment Modal -->
                            <div class="modal modal-blur fade" id="payment-modal-{{ $item->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Record Payment</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('progress-items.payment', $item) }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Progress Item</label>
                                                    <input type="text" class="form-control" disabled value="{{ $item->title }}">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Remaining Amount</label>
                                                    <input type="text" class="form-control" disabled value="{{ number_format($item->remaining_amount, 2) }} €">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Payment Amount</label>
                                                    <input type="number" step="0.01" max="{{ $item->remaining_amount }}" name="payment_amount" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Record Payment</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="empty">
                                        <div class="empty-img"><img src="{{ asset('static/illustrations/undraw_printing_invoices_5r4r.svg') }}" height="128" alt="">
                                        </div>
                                        <p class="empty-title">No progress items found</p>
                                        <p class="empty-subtitle text-muted">
                                            Start adding development progress items to track your work and invoicing.
                                        </p>
                                        <div class="empty-action">
                                            <a href="{{ route('progress-items.create') }}" class="btn btn-primary">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M12 5l0 14" />
                                                    <path d="M5 12l14 0" />
                                                </svg>
                                                Add your first progress item
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 