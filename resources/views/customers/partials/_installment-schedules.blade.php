@if ($customer->paymentSchedules->count())
    <div class="mt-4" x-data="{
        selectedSchedules: [],
        showPayModal: false,
        payEntryId: null,
        payDate: '{{ now()->format('d/m/Y') }}',
        payAmount: '',
        payRemaining: 0,
        payNumber: '',
        payType: 'HandCash',

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

        openPayModal(entryId, remaining, number) {
            this.payEntryId = entryId;
            this.payRemaining = remaining;
            this.payAmount = remaining;
            this.payNumber = number;
            this.payDate = '{{ now()->format('d/m/Y') }}';
            this.payType = 'HandCash';
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

                @php $guarantee = $schedule->guarantee; @endphp
                <div class="px-3 py-2 border-bottom bg-light-subtle">
                    @if ($guarantee)
                        <button type="button"
                                class="btn btn-sm btn-link text-decoration-none p-0 align-baseline"
                                @click="Livewire.dispatch('guarantee:prepare', { scheduleId: {{ $schedule->id }} })">
                            <i class="fas fa-shield-alt text-primary me-1"></i>
                            @if ($guarantee->type === 'cheque')
                                <span class="text-muted small">{{ __('Guarantee · Cheque') }}:</span>
                                <strong>{{ $guarantee->cheque_bank ?? '—' }}</strong>
                                @if ($guarantee->cheque_nature)
                                    #{{ $guarantee->cheque_nature }}
                                @endif
                                @if ($guarantee->cheque_amount)
                                    — {{ Number::currency($guarantee->cheque_amount, 'MAD') }}
                                @endif
                            @else
                                <span class="text-muted small">{{ __('Guarantee · Person') }}:</span>
                                <strong>{{ $guarantee->person?->name ?? __('Deleted customer') }}</strong>
                                @if ($guarantee->person?->cin)
                                    ({{ $guarantee->person->cin }})
                                @endif
                            @endif
                            <i class="fas fa-pen text-muted small ms-2"></i>
                        </button>
                    @else
                        <button type="button"
                                class="btn btn-sm btn-link text-muted text-decoration-none p-0 align-baseline"
                                @click="Livewire.dispatch('guarantee:prepare', { scheduleId: {{ $schedule->id }} })">
                            <i class="fas fa-shield-alt me-1"></i>{{ __('Add guarantee (Person · Cheque)') }}
                        </button>
                    @endif
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
                                @php
                                    $remaining = (float) $entry->amount - (float) $entry->paid_amount;
                                @endphp
                                <tr>
                                    <td>{{ $entry->installment_number }}</td>
                                    <td>
                                        {{ Number::currency($entry->amount, 'MAD') }}
                                        @if ((float) $entry->paid_amount > 0 && $entry->status !== 'paid')
                                            <div class="small text-muted">
                                                {{ __('Paid') }}: {{ Number::currency($entry->paid_amount, 'MAD') }}
                                                · {{ __('Remaining') }}: <span class="fw-bold">{{ Number::currency($remaining, 'MAD') }}</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $entry->due_date->format('d/m/Y') }}</td>
                                    <td>
                                        @if ($entry->status === 'paid')
                                            <span class="badge bg-success">{{ __('Paid') }}</span>
                                        @elseif ($entry->status === 'partial')
                                            <span class="badge bg-orange-lt">{{ __('Partial') }}</span>
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
                                                @click="openPayModal({{ $entry->id }}, {{ $remaining }}, '{{ $entry->installment_number }}')">
                                                <i class="fas fa-plus me-1"></i>{{ __('Add Payment') }}
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

        {{-- Add Payment Modal --}}
        <div class="modal modal-blur" :class="{ 'show d-block': showPayModal }" tabindex="-1"
             x-show="showPayModal" x-cloak @click.self="showPayModal = false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Add Payment') }}</h5>
                        <button type="button" class="btn-close" @click="showPayModal = false"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3 text-muted small">
                            {{ __('Installment') }} #<span x-text="payNumber"></span> — {{ __('Remaining') }}: <span x-text="payRemaining.toFixed(2) + ' MAD'" class="fw-bold text-dark"></span>
                        </p>
                        <div class="mb-3">
                            <label class="form-label required">{{ __('Amount') }} (MAD)</label>
                            <input type="number" step="0.01" min="0.01" class="form-control"
                                   x-model.number="payAmount" :max="payRemaining">
                            <div class="form-hint">{{ __('Defaults to remaining; lower for partial.') }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">{{ __('Payment Type') }}</label>
                            <select class="form-select" x-model="payType">
                                <option value="HandCash">{{ __('HandCash') }}</option>
                                <option value="Cheque">{{ __('Cheque') }}</option>
                                <option value="Exchange">{{ __('Exchange') }}</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">{{ __('Payment Date') }}</label>
                            <input type="text" class="form-control" x-model="payDate" placeholder="dd/mm/yyyy"
                                   @input="formatDateField($event)">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn" @click="showPayModal = false">{{ __('Cancel') }}</button>
                        <form :action="'/installments/' + payEntryId + '/pay'" method="POST">
                            @csrf
                            <input type="hidden" name="paid_date" :value="payDate">
                            <input type="hidden" name="amount" :value="payAmount">
                            <input type="hidden" name="payment_type" :value="payType">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check me-1"></i>{{ __('Confirm') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show" x-show="showPayModal" x-cloak @click="showPayModal = false"></div>

        @livewire('installment-guarantee-form')
    </div>
@endif
