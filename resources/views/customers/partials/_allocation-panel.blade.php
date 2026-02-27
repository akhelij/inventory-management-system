<style>
    [draggable="true"]:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transform: translateY(-1px);
        transition: all 0.2s ease;
    }
    @keyframes slideIntoChip {
        0% { transform: scale(1.5); opacity: 0; }
        50% { transform: scale(1.1); opacity: 0.8; }
        100% { transform: scale(1); opacity: 1; }
    }
    @keyframes chipPopOut {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.3); opacity: 0.8; }
        100% { transform: scale(1.5); opacity: 0; }
    }
    .badge.animating-in { animation: slideIntoChip 0.4s ease-out; }
    .badge.animating-out { animation: chipPopOut 0.3s ease-in; }
    .progress-bar { transition: width 0.5s ease; }
</style>

<div x-data="allocationPanel()" class="row" style="margin-left:-20px; margin-top:1%">
    <!-- Orders Column -->
    <div class="col-5">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('Orders') }}</h3>
                <div class="card-actions">
                    <x-status dot color="green" class="btn btn-sm">
                        {{ $totalOrders }} MAD
                    </x-status>
                </div>
            </div>
            <div class="card-body p-2" style="max-height: 600px; overflow-y: auto;">
                <template x-for="order in orders" :key="order.id">
                    <div class="card mb-2"
                         :class="{
                             'border-success border-2': order.drop_hover && order.due > 0,
                             'border-secondary opacity-50': order.status !== 'Approved',
                             'bg-success-lt': order.due === 0
                         }"
                         @dragover.prevent="handleDragOver($event, order)"
                         @dragleave="handleDragLeave(order)"
                         @drop.prevent="handleDrop($event, order)">
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <a :href="'/orders/' + order.uuid" target="_blank"
                                       class="text-decoration-none fw-bold"
                                       x-text="order.invoice_no"></a>
                                    <small class="text-muted d-block" x-text="order.order_date"></small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold" x-text="formatCurrency(order.total)"></div>
                                    <small class="text-muted"
                                           x-show="order.due > 0"
                                           x-text="'{{ __('Due') }}: ' + formatCurrency(order.due)"></small>
                                    <span class="badge bg-success" x-show="order.due === 0">
                                        {{ __('Fully Paid') }}
                                    </span>
                                </div>
                            </div>

                            <!-- Allocated payment chips -->
                            <div class="mt-2 d-flex flex-wrap gap-1" x-show="order.allocations.length > 0">
                                <template x-for="alloc in order.allocations" :key="alloc.payment_id">
                                    <span class="badge bg-blue-lt" style="cursor: pointer;">
                                        <span x-text="alloc.nature + ': ' + formatCurrency(alloc.allocated_amount)"></span>
                                        <button type="button" class="ms-1 border-0 bg-transparent p-0 lh-1"
                                                style="font-size: 0.65rem; color: #206bc4; opacity: 0.7;"
                                                @click="detachAllocation(order, alloc)"
                                                :disabled="loading"
                                                title="{{ __('Remove allocation') }}">✕</button>
                                    </span>
                                </template>
                            </div>

                            <!-- Drop zone hint -->
                            <div class="text-center text-success mt-2 small"
                                 x-show="order.drop_hover && order.due > 0"
                                 x-cloak
                                 x-text="'{{ __('Will allocate') }} ' + formatCurrency(getPreviewAmount(order))">
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Payments Column -->
    <div class="col-7">
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">{{ __('Payments') }}</h3>
                </div>
                <div class="card-actions">
                    <x-status dot color="green" class="btn btn-sm">
                        <small>{{ __('Paid') }}:</small> {{ $totalPayments }} MAD
                    </x-status>
                    <x-status dot color="orange" class="btn btn-sm">
                        <small>{{ __('Pending') }}:</small> {{ $amountPendingPayments }} MAD
                    </x-status>
                    <x-status dot color="red" class="btn btn-sm">
                        <small>{{ __('Due') }}:</small> {{ $due }} MAD
                    </x-status>
                    <a href="{{ '/payments/'.$customer->id.'/create'}}" class="btn btn-sm btn-primary" title="{{ __('Add Payment') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M12 5l0 14"/>
                            <path d="M5 12l14 0"/>
                        </svg>
                    </a>
                </div>
            </div>
            <div class="card-body p-2" style="max-height: 600px; overflow-y: auto;">
                <template x-for="payment in payments" :key="payment.id">
                    <div class="card mb-2"
                         :class="{
                             'opacity-50': payment.is_fully_allocated,
                             'border-primary': payment.dragging,
                         }"
                         :draggable="allocationEnabled && !payment.is_fully_allocated && !loading"
                         @dragstart="handleDragStart($event, payment)"
                         @dragend="handleDragEnd(payment)"
                         :style="allocationEnabled && !payment.is_fully_allocated ? 'cursor: grab;' : ''">
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-bold" x-text="payment.nature"></span>
                                    <small class="text-muted d-block" x-text="payment.payment_type"></small>
                                    <small class="text-muted" x-text="payment.date"></small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold" x-text="formatCurrency(payment.amount)"></div>
                                    <template x-if="payment.cashed_in">
                                        <div>
                                            <small class="text-success" x-show="payment.is_fully_allocated">
                                                {{ __('Fully Allocated') }}
                                            </small>
                                            <small class="text-info"
                                                   x-show="!payment.is_fully_allocated && payment.unallocated_amount < payment.amount"
                                                   x-text="'{{ __('Available') }}: ' + formatCurrency(payment.unallocated_amount)">
                                            </small>
                                        </div>
                                    </template>
                                </div>
                                <div>
                                    <template x-if="payment.reported">
                                        <x-status dot color="red" class="btn btn-sm">
                                            <small>{{ __('Reported') }}</small>
                                        </x-status>
                                    </template>
                                    <template x-if="payment.cashed_in && !payment.reported">
                                        <x-status dot color="green" class="btn btn-sm">
                                            <small>{{ __('Cashed In') }}</small>
                                        </x-status>
                                    </template>
                                    <template x-if="!payment.cashed_in && !payment.reported">
                                        <x-status dot color="orange" class="btn btn-sm">
                                            <small>{{ __('Pending') }}</small>
                                        </x-status>
                                    </template>
                                </div>
                            </div>

                            <!-- Allocation progress bar -->
                            <template x-if="payment.cashed_in && payment.amount > 0">
                                <div class="mt-2">
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-success"
                                             :style="'width: ' + ((payment.amount - payment.unallocated_amount) / payment.amount * 100) + '%'">
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- Action buttons for uncashed payments -->
                            <div class="d-flex align-items-center gap-1 mt-2"
                                 x-show="!payment.cashed_in || payment.reported">
                                <template x-if="!payment.reported && !payment.cashed_in">
                                    <form class="reportForm"
                                          :action="'/payments/' + payment.id + '/report'"
                                          method="POST">
                                        @csrf
                                        <button class="reportButton btn btn-sm btn-icon btn-warning"
                                                type="submit" title="{{ __('Report') }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                <path d="M12 21a9 9 0 0 0 0 -18c-4 0 -7.5 1.5 -9 4.5v-2.5h-3v8h8v-3h-2.5c1.5 -2.5 4 -4 6.5 -4a6 6 0 0 1 6 6m0 -3v6"/>
                                            </svg>
                                        </button>
                                    </form>
                                </template>
                                <template x-if="!payment.cashed_in">
                                    <form :action="'/payments/' + payment.id + '/cash-in'"
                                          method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-icon btn-primary" title="{{ __('Cash In') }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                <path d="M5 12l5 5l10 -10"/>
                                            </svg>
                                        </button>
                                    </form>
                                </template>
                                <template x-if="!payment.cashed_in">
                                    <form :action="'/payments/' + payment.id"
                                          method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-danger" title="{{ __('Delete') }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                <path d="M18 6l-12 12"/>
                                                <path d="M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </form>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Loading overlay -->
    <template x-if="loading">
        <div class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
             style="background: rgba(0,0,0,0.1); z-index: 9999;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </template>
</div>

@php
    $ordersData = $customer->orders->map(function ($order) {
        $allocations = $order->relationLoaded('payments')
            ? $order->payments->map(fn ($p) => [
                'payment_id' => $p->id,
                'nature' => $p->nature,
                'allocated_amount' => $p->pivot->allocated_amount,
            ])->values()
            : collect();

        return [
            'id' => $order->id,
            'uuid' => $order->uuid,
            'invoice_no' => $order->invoice_no,
            'order_date' => $order->order_date?->format('d/m/Y'),
            'total' => $order->total,
            'pay' => $order->pay,
            'due' => $order->due,
            'status' => $order->status,
            'allocations' => $allocations,
            'drop_hover' => false,
        ];
    })->values();

    $paymentsData = $customer->payments->map(fn ($payment) => [
        'id' => $payment->id,
        'nature' => $payment->nature,
        'payment_type' => $payment->payment_type,
        'date' => $payment->date,
        'amount' => $payment->amount,
        'cashed_in' => (bool) $payment->cashed_in,
        'reported' => (bool) $payment->reported,
        'unallocated_amount' => $payment->unallocated_amount,
        'is_fully_allocated' => $payment->is_fully_allocated,
        'dragging' => false,
    ])->values();
@endphp

<script>
function allocationPanel() {
    return {
        orders: @json($ordersData),

        payments: @json($paymentsData),

        allocationEnabled: @json($allocationEnabled ?? false),

        draggedPayment: null,
        loading: false,

        formatCurrency(amount) {
            return new Intl.NumberFormat('fr-MA', {
                style: 'currency',
                currency: 'MAD',
                minimumFractionDigits: 2,
            }).format(amount);
        },

        handleDragStart(event, payment) {
            this.draggedPayment = payment;
            payment.dragging = true;
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', payment.id);
        },

        handleDragEnd(payment) {
            payment.dragging = false;
            this.draggedPayment = null;
            this.orders.forEach(o => o.drop_hover = false);
        },

        handleDragOver(event, order) {
            if (!this.draggedPayment) return;
            if (order.status !== 'Approved' || order.due <= 0) return;
            event.dataTransfer.dropEffect = 'move';
            order.drop_hover = true;
        },

        handleDragLeave(order) {
            order.drop_hover = false;
        },

        getPreviewAmount(order) {
            if (!this.draggedPayment) return 0;
            return Math.min(this.draggedPayment.unallocated_amount, order.due);
        },

        async handleDrop(event, order) {
            order.drop_hover = false;
            if (!this.draggedPayment) return;
            if (order.status !== 'Approved' || order.due <= 0) return;

            const payment = this.draggedPayment;
            this.loading = true;

            try {
                const response = await fetch(`/api/orders/${order.id}/payments/${payment.id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json();

                if (!response.ok) {
                    alert(data.message || 'Failed to allocate payment.');
                    return;
                }

                order.pay = data.order.pay;
                order.due = data.order.due;
                order.allocations.push({
                    payment_id: payment.id,
                    nature: payment.nature,
                    allocated_amount: data.allocated_amount,
                });

                // Animate the new chip
                this.$nextTick(() => {
                    const chips = this.$el.querySelectorAll('.badge.bg-blue-lt');
                    const lastChip = chips[chips.length - 1];
                    if (lastChip) {
                        lastChip.classList.add('animating-in');
                        setTimeout(() => lastChip.classList.remove('animating-in'), 400);
                    }
                });

                payment.unallocated_amount = data.payment.unallocated_amount;
                payment.is_fully_allocated = data.payment.is_fully_allocated;

            } catch (error) {
                console.error('Allocation error:', error);
                alert('Network error. Please try again.');
            } finally {
                this.loading = false;
            }
        },

        async detachAllocation(order, alloc) {
            if (!confirm('{{ __('Remove this payment allocation?') }}')) return;

            this.loading = true;

            try {
                const response = await fetch(`/api/orders/${order.id}/payments/${alloc.payment_id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json();

                if (!response.ok) {
                    alert(data.message || 'Failed to remove allocation.');
                    return;
                }

                order.pay = data.order.pay;
                order.due = data.order.due;
                order.allocations = order.allocations.filter(a => a.payment_id !== alloc.payment_id);

                const paymentObj = this.payments.find(p => p.id === alloc.payment_id);
                if (paymentObj) {
                    paymentObj.unallocated_amount = data.payment.unallocated_amount;
                    paymentObj.is_fully_allocated = data.payment.is_fully_allocated;
                }

            } catch (error) {
                console.error('Detach error:', error);
                alert('Network error. Please try again.');
            } finally {
                this.loading = false;
            }
        },
    };
}
</script>
