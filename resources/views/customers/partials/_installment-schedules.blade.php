@if ($customer->paymentSchedules->count())
    <div class="mt-4" x-data="{
        selectedSchedules: [],
        showPayModal: false,
        payEntryId: null,
        payDate: '{{ now()->format('d/m/Y') }}',
        payAmount: '',
        payNumber: '',

        toggleSchedule(id) {
            const idx = this.selectedSchedules.indexOf(id);
            if (idx > -1) this.selectedSchedules.splice(idx, 1);
            else this.selectedSchedules.push(id);
        },

        formatDateField(event) {
            let v = event.target.value.replace(/\D/g, '');
            if (v.length >= 2) v = v.substring(0, 2) + '/' + v.substring(2);
            if (v.length >= 5) v = v.substring(0, 5) + '/' + v.substring(5, 9);
            event.target.value = v;
            this.payDate = v;
        },

        openPayModal(entryId, amount, number) {
            this.payEntryId = entryId;
            this.payAmount = amount;
            this.payNumber = number;
            this.payDate = '{{ now()->format('d/m/Y') }}';
            this.showPayModal = true;
        }
    }">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h3 class="mb-0">{{ __('Payment Schedules') }}</h3>
            <form method="POST" action="{{ route('payment-schedules.export', $customer) }}"
                  x-show="selectedSchedules.length > 0" x-cloak>
                @csrf
                <template x-for="id in selectedSchedules" :key="id">
                    <input type="hidden" name="schedule_ids[]" :value="id">
                </template>
                <button type="submit" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-print me-1"></i>{{ __('Print Selected') }}
                    <span class="badge bg-primary ms-1" x-text="selectedSchedules.length"></span>
                </button>
            </form>
        </div>

        @foreach ($customer->paymentSchedules as $schedule)
            <div class="card mb-3">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <label class="form-check me-2 mb-0">
                            <input type="checkbox" class="form-check-input"
                                   :checked="selectedSchedules.includes({{ $schedule->id }})"
                                   @change="toggleSchedule({{ $schedule->id }})">
                        </label>
                        <h3 class="card-title mb-0">
                            {{ __('Order') }}: {{ $schedule->order->invoice_no }}
                            <span class="badge bg-blue-lt ms-2">{{ $schedule->total_installments }}x {{ __('every') }} {{ $schedule->period_days }} {{ __('days') }}</span>
                        </h3>
                    </div>
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
                            @if ($schedule->advance_amount > 0)
                                <tr class="table-warning">
                                    <td><i class="fas fa-hand-holding-usd"></i></td>
                                    <td class="fw-bold">{{ Number::currency($schedule->advance_amount, 'MAD') }}</td>
                                    <td>{{ $schedule->advancePayment?->date ?? '-' }}</td>
                                    <td>
                                        @if ($schedule->advancePayment?->cashed_in)
                                            <span class="badge bg-success">{{ __('Cashed In') }}</span>
                                        @else
                                            <span class="badge bg-orange">{{ __('Pending') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $schedule->advancePayment?->cashed_in_at?->format('d/m/Y') ?? '-' }}</td>
                                    <td class="text-end">
                                        <span class="badge bg-blue-lt">{{ __('Advance') }}</span>
                                    </td>
                                </tr>
                            @endif

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
                                            <button type="button" class="btn btn-sm btn-success"
                                                @click="openPayModal({{ $entry->id }}, '{{ Number::currency($entry->amount, 'MAD') }}', '{{ $entry->installment_number }}')">
                                                <i class="fas fa-check me-1"></i>{{ __('Mark Paid') }}
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach

        {{-- Mark Paid Modal --}}
        <div class="modal modal-blur" :class="{ 'show d-block': showPayModal }" tabindex="-1"
             x-show="showPayModal" x-cloak @click.self="showPayModal = false">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Mark Installment as Paid') }}</h5>
                        <button type="button" class="btn-close" @click="showPayModal = false"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">
                            {{ __('Installment') }} #<span x-text="payNumber"></span> — <span x-text="payAmount"></span>
                        </p>
                        <label class="form-label">{{ __('Payment Date') }}</label>
                        <input type="text" class="form-control" x-model="payDate" placeholder="dd/mm/yyyy"
                               @input="formatDateField($event)">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn" @click="showPayModal = false">{{ __('Cancel') }}</button>
                        <form :action="'/installments/' + payEntryId + '/pay'" method="POST">
                            @csrf
                            <input type="hidden" name="paid_date" :value="payDate">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check me-1"></i>{{ __('Confirm') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show" x-show="showPayModal" x-cloak @click="showPayModal = false"></div>
    </div>
@endif
