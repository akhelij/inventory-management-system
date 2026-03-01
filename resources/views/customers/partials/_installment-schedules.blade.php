@if ($customer->paymentSchedules->count())
    <div class="mt-4">
        <h3 class="mb-3">{{ __('Payment Schedules') }}</h3>

        @foreach ($customer->paymentSchedules as $schedule)
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">
                        {{ __('Order') }}: {{ $schedule->order->invoice_no }}
                        <span class="badge bg-blue-lt ms-2">{{ $schedule->total_installments }}x {{ __('every') }} {{ $schedule->period_days }} {{ __('days') }}</span>
                    </h3>
                    <div class="card-actions">
                        <span class="text-muted small">{{ Number::currency($schedule->total_amount, 'MAD') }}</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>{{ __('#') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Due Date') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Paid At') }}</th>
                                <th class="text-end">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($schedule->entries as $entry)
                                <tr>
                                    <td>{{ $entry->installment_number }}</td>
                                    <td>{{ Number::currency($entry->amount, 'MAD') }}</td>
                                    <td>{{ $entry->due_date->format('d/m/Y') }}</td>
                                    <td>
                                        @if ($entry->status === 'paid')
                                            <span class="badge bg-success">{{ __('Paid') }}</span>
                                        @elseif ($entry->status === 'overdue')
                                            <span class="badge bg-danger">{{ __('Overdue') }}</span>
                                        @else
                                            <span class="badge bg-warning">{{ __('Pending') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $entry->paid_at?->format('d/m/Y') ?? '-' }}</td>
                                    <td class="text-end">
                                        @if ($entry->status !== 'paid')
                                            <form action="{{ route('installments.pay', $entry) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="fas fa-check me-1"></i>{{ __('Mark Paid') }}
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
@endif
