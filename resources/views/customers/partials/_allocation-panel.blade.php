<style>
    [draggable="true"]:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transform: translateY(-1px);
        transition: all 0.2s ease;
    }
    @keyframes slideIn {
        from { transform: scale(0.9); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
    @keyframes chipPop {
        0% { transform: scale(1.5); opacity: 0; }
        50% { transform: scale(1.1); opacity: 0.8; }
        100% { transform: scale(1); opacity: 1; }
    }
    .chip-animate { animation: chipPop 0.4s ease-out; }
    .card-animate { animation: slideIn 0.3s ease-out; }
    .progress-bar { transition: width 0.5s ease; }

    /* Modal — pure CSS, Tailwind-inspired clean design */
    .payment-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(107, 114, 128, 0.5);
        z-index: 9998;
        backdrop-filter: blur(2px);
    }
    .payment-modal-container {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .payment-modal-box {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(0, 0, 0, 0.05);
        width: 100%;
        max-width: 540px;
        margin: 1rem;
        animation: slideIn 0.2s ease-out;
        overflow: hidden;
    }
    .payment-modal-box .modal-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin: 1.25rem 1.5rem;
    }
    .payment-modal-box .modal-head h3 {
        font-size: 1.1rem;
        font-weight: 600;
        color: #111827;
        margin: 0;
    }
    .payment-modal-box .modal-head .close-btn {
        width: 2rem;
        height: 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        background: #f3f4f6;
        border-radius: 8px;
        color: #6b7280;
        cursor: pointer;
        transition: all 0.15s;
    }
    .payment-modal-box .modal-head .close-btn:hover {
        background: #e5e7eb;
        color: #374151;
    }
    .payment-modal-box .modal-divider {
        height: 1px;
        background: #e5e7eb;
        margin: 0 1.5rem;
    }
    .payment-modal-box .modal-content-area {
        margin: 1.25rem 1.5rem;
    }
    .payment-modal-box .modal-content-area .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    .payment-modal-box .modal-content-area .form-grid .full-width {
        grid-column: 1 / -1;
    }
    .payment-modal-box .modal-content-area .form-label {
        font-size: 0.8rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.35rem;
    }
    .payment-modal-box .modal-content-area .form-control,
    .payment-modal-box .modal-content-area .form-select {
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 0.875rem;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .payment-modal-box .modal-content-area .form-control:focus,
    .payment-modal-box .modal-content-area .form-select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    .payment-modal-box .modal-foot {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.75rem;
        margin: 1.25rem 1.5rem;
    }
    .payment-modal-box .modal-foot .btn-cancel {
        border: 1px solid #d1d5db;
        background: #fff;
        color: #374151;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.15s;
    }
    .payment-modal-box .modal-foot .btn-cancel:hover {
        background: #f9fafb;
        border-color: #9ca3af;
    }
    .payment-modal-box .modal-foot .btn-submit {
        background: #3b82f6;
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.15s;
    }
    .payment-modal-box .modal-foot .btn-submit:hover:not(:disabled) {
        background: #2563eb;
    }
    .payment-modal-box .modal-foot .btn-submit:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    .payment-modal-box .auto-alloc-hint {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        color: #1e40af;
        font-size: 0.8rem;
        margin-top: 1rem;
    }
    .payment-modal-box .modal-errors {
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 8px;
        color: #991b1b;
        font-size: 0.8rem;
        margin-bottom: 1rem;
    }
</style>

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
        'echeance' => $payment->echeance,
        'amount' => $payment->amount,
        'cashed_in' => (bool) $payment->cashed_in,
        'reported' => (bool) $payment->reported,
        'unallocated_amount' => $payment->unallocated_amount,
        'is_fully_allocated' => $payment->is_fully_allocated,
        'dragging' => false,
    ])->values();
@endphp

<div x-data="{
    orders: {{ Js::from($ordersData) }},
    payments: {{ Js::from($paymentsData) }},
    allocationEnabled: {{ Js::from($allocationEnabled ?? false) }},
    draggedPayment: null,
    loading: false,
    showModal: false,
    modalSubmitting: false,
    modalOrderId: null,
    modalOrderInvoice: '',
    modalErrors: {},
    form: {
        nature: '',
        payment_type: 'HandCash',
        bank: '',
        date: new Date().toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' }),
        echeance: '',
        amount: '',
        description: '',
    },

    formatCurrency(amount) {
        return new Intl.NumberFormat('fr-MA', { style: 'currency', currency: 'MAD', minimumFractionDigits: 2 }).format(amount);
    },

    openModal(order = null) {
        this.modalErrors = {};
        this.form = {
            nature: '',
            payment_type: 'HandCash',
            bank: '',
            date: new Date().toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' }),
            echeance: '',
            amount: order ? order.due : '',
            description: '',
        };
        this.modalOrderId = order ? order.id : null;
        this.modalOrderInvoice = order ? order.invoice_no : '';
        this.modalSubmitting = false;
        this.showModal = true;
    },

    closeModal() {
        this.showModal = false;
        this.modalErrors = {};
    },

    formatDateField(event) {
        let v = event.target.value.replace(/\D/g, '');
        if (v.length >= 2) v = v.substring(0, 2) + '/' + v.substring(2);
        if (v.length >= 5) v = v.substring(0, 5) + '/' + v.substring(5, 9);
        event.target.value = v;
        const field = event.target.dataset.field;
        if (field) this.form[field] = v;
    },

    async submitPayment() {
        this.modalSubmitting = true;
        this.modalErrors = {};

        const body = {
            customer_id: {{ $customer->id }},
            nature: this.form.nature,
            payment_type: this.form.payment_type,
            bank: this.form.bank || null,
            date: this.form.date,
            echeance: this.form.echeance,
            amount: this.form.amount,
            description: this.form.description || null,
        };
        if (this.modalOrderId) body.order_id = this.modalOrderId;

        try {
            const res = await fetch('/payments/{{ $customer->id }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(body),
            });
            const data = await res.json();

            if (!res.ok) {
                if (res.status === 422 && data.errors) this.modalErrors = data.errors;
                else alert(data.message || 'Error creating payment.');
                return;
            }

            this.payments.unshift(data.payment);
            this.$nextTick(() => {
                const card = this.$el.querySelector('.col-7 .card-body .card');
                if (card) { card.classList.add('card-animate'); setTimeout(() => card.classList.remove('card-animate'), 400); }
            });

            if (data.allocation) {
                const order = this.orders.find(o => o.id === this.modalOrderId);
                if (order) {
                    order.pay = data.allocation.order.pay;
                    order.due = data.allocation.order.due;
                    order.allocations.push({
                        payment_id: data.payment.id,
                        nature: data.payment.nature,
                        allocated_amount: data.allocation.allocated_amount,
                    });
                }
            }
            this.closeModal();
        } catch (e) {
            console.error('Payment creation error:', e);
            alert('Network error. Please try again.');
        } finally {
            this.modalSubmitting = false;
        }
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
        if (!this.draggedPayment || order.status !== 'Approved' || order.due <= 0) return;
        event.dataTransfer.dropEffect = 'move';
        order.drop_hover = true;
    },
    handleDragLeave(order) { order.drop_hover = false; },
    getPreviewAmount(order) {
        return this.draggedPayment ? Math.min(this.draggedPayment.unallocated_amount, order.due) : 0;
    },

    async handleDrop(event, order) {
        order.drop_hover = false;
        if (!this.draggedPayment || order.status !== 'Approved' || order.due <= 0) return;
        const payment = this.draggedPayment;
        this.loading = true;
        try {
            const res = await fetch(`/api/orders/${order.id}/payments/${payment.id}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
            });
            const data = await res.json();
            if (!res.ok) { alert(data.message || 'Failed to allocate payment.'); return; }

            order.pay = data.order.pay;
            order.due = data.order.due;
            order.allocations.push({ payment_id: payment.id, nature: payment.nature, allocated_amount: data.allocated_amount });

            this.$nextTick(() => {
                const chips = this.$el.querySelectorAll('.badge.bg-blue-lt');
                const last = chips[chips.length - 1];
                if (last) { last.classList.add('chip-animate'); setTimeout(() => last.classList.remove('chip-animate'), 400); }
            });

            payment.unallocated_amount = data.payment.unallocated_amount;
            payment.is_fully_allocated = data.payment.is_fully_allocated;
        } catch (e) { console.error('Allocation error:', e); alert('Network error.'); }
        finally { this.loading = false; }
    },

    async detachAllocation(order, alloc) {
        if (!confirm('{{ __('Remove this payment allocation?') }}')) return;
        this.loading = true;
        try {
            const res = await fetch(`/api/orders/${order.id}/payments/${alloc.payment_id}`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
            });
            const data = await res.json();
            if (!res.ok) { alert(data.message || 'Failed to remove allocation.'); return; }

            order.pay = data.order.pay;
            order.due = data.order.due;
            order.allocations = order.allocations.filter(a => a.payment_id !== alloc.payment_id);

            const p = this.payments.find(p => p.id === alloc.payment_id);
            if (p) { p.unallocated_amount = data.payment.unallocated_amount; p.is_fully_allocated = data.payment.is_fully_allocated; }
        } catch (e) { console.error('Detach error:', e); alert('Network error.'); }
        finally { this.loading = false; }
    },
}" class="row" style="margin-left:-20px; margin-top:1%">

    <!-- ==================== ORDERS COLUMN ==================== -->
    <div class="col-5">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('Orders') }}</h3>
                <div class="card-actions">
                    <x-status dot color="green" class="btn btn-sm">{{ $totalOrders }} MAD</x-status>
                </div>
            </div>
            <div class="card-body p-2" style="max-height: 600px; overflow-y: auto;">
                <template x-for="order in orders" :key="order.id">
                    <div class="card mb-2"
                         :class="{
                             'border-success border-2': order.drop_hover && order.due > 0,
                             'border-secondary opacity-50': order.status !== 'Approved',
                             'bg-success-lt': order.due === 0,
                         }"
                         @dragover.prevent="handleDragOver($event, order)"
                         @dragleave="handleDragLeave(order)"
                         @drop.prevent="handleDrop($event, order)">
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <a :href="'/orders/' + order.uuid" target="_blank"
                                       class="text-decoration-none fw-bold" x-text="order.invoice_no"></a>
                                    <small class="text-muted d-block" x-text="order.order_date"></small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold" x-text="formatCurrency(order.total)"></div>
                                    <small class="text-muted" x-show="order.due > 0"
                                           x-text="'{{ __('Due') }}: ' + formatCurrency(order.due)"></small>
                                    <button class="btn btn-sm btn-outline-primary mt-1 py-0 px-1"
                                            x-show="order.due > 0 && order.status === 'Approved'"
                                            @click="openModal(order)"
                                            style="font-size: 0.7rem;">{{ __('Pay') }}</button>
                                    <span class="badge bg-success" x-show="order.due === 0">{{ __('Fully Paid') }}</span>
                                </div>
                            </div>

                            <div class="mt-2 d-flex flex-wrap gap-1" x-show="order.allocations.length > 0">
                                <template x-for="alloc in order.allocations" :key="alloc.payment_id">
                                    <span class="badge bg-blue-lt" style="cursor: pointer;">
                                        <span x-text="alloc.nature + ': ' + formatCurrency(alloc.allocated_amount)"></span>
                                        <button type="button" class="ms-1 border-0 bg-transparent p-0 lh-1"
                                                style="font-size: 0.65rem; color: #206bc4; opacity: 0.7;"
                                                @click="detachAllocation(order, alloc)" :disabled="loading"
                                                title="{{ __('Remove allocation') }}">✕</button>
                                    </span>
                                </template>
                            </div>

                            <div class="text-center text-success mt-2 small"
                                 x-show="order.drop_hover && order.due > 0" x-cloak
                                 x-text="'{{ __('Will allocate') }} ' + formatCurrency(getPreviewAmount(order))"></div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- ==================== PAYMENTS COLUMN ==================== -->
    <div class="col-7">
        <div class="card">
            <div class="card-header">
                <div><h3 class="card-title">{{ __('Payments') }}</h3></div>
                <div class="card-actions">
                    <x-status dot color="green" class="btn btn-sm"><small>{{ __('Paid') }}:</small> {{ $totalPayments }} MAD</x-status>
                    <x-status dot color="orange" class="btn btn-sm"><small>{{ __('Pending') }}:</small> {{ $amountPendingPayments }} MAD</x-status>
                    <x-status dot color="red" class="btn btn-sm"><small>{{ __('Due') }}:</small> {{ $due }} MAD</x-status>
                    <button type="button" class="btn btn-sm btn-primary" title="{{ __('Add Payment') }}" @click="openModal()">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24"
                             stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="card-body p-2" style="max-height: 600px; overflow-y: auto;">
                <template x-for="payment in payments" :key="payment.id">
                    <div class="card mb-2"
                         :class="{ 'opacity-50': payment.is_fully_allocated, 'border-primary': payment.dragging }"
                         :draggable="allocationEnabled && !payment.is_fully_allocated && !loading"
                         @dragstart="handleDragStart($event, payment)"
                         @dragend="handleDragEnd(payment)"
                         :style="allocationEnabled && !payment.is_fully_allocated ? 'cursor: grab;' : ''">
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="fw-bold" x-text="payment.nature"></span>
                                    <small class="text-muted ms-1" x-text="payment.payment_type"></small>
                                    <div class="mt-1">
                                        <small class="text-muted">{{ __('Created') }}: <span x-text="payment.date"></span></small>
                                        <template x-if="payment.echeance">
                                            <small class="text-muted d-block">{{ __('Due') }}: <span x-text="payment.echeance"></span></small>
                                        </template>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold" x-text="formatCurrency(payment.amount)"></span>
                                    <div class="mt-1">
                                        <template x-if="payment.reported">
                                            <x-status dot color="red"><small>{{ __('Reported') }}</small></x-status>
                                        </template>
                                        <template x-if="payment.cashed_in && !payment.reported">
                                            <x-status dot color="green"><small>{{ __('Cashed In') }}</small></x-status>
                                        </template>
                                        <template x-if="!payment.cashed_in && !payment.reported">
                                            <x-status dot color="orange"><small>{{ __('Pending') }}</small></x-status>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <template x-if="payment.amount > 0 && payment.unallocated_amount < payment.amount">
                                <div class="mt-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="text-success" x-show="payment.is_fully_allocated">{{ __('Fully Allocated') }}</small>
                                        <small class="text-info" x-show="!payment.is_fully_allocated"
                                               x-text="'{{ __('Available') }}: ' + formatCurrency(payment.unallocated_amount)"></small>
                                    </div>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-success"
                                             :style="'width: ' + ((payment.amount - payment.unallocated_amount) / payment.amount * 100) + '%'"></div>
                                    </div>
                                </div>
                            </template>

                            <div class="d-flex align-items-center gap-1 mt-2" x-show="!payment.cashed_in || payment.reported">
                                <template x-if="!payment.reported && !payment.cashed_in">
                                    <form class="reportForm" :action="'/payments/' + payment.id + '/report'" method="POST">
                                        @csrf
                                        <button class="reportButton btn btn-sm btn-icon btn-warning" type="submit" title="{{ __('Report') }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 21a9 9 0 0 0 0 -18c-4 0 -7.5 1.5 -9 4.5v-2.5h-3v8h8v-3h-2.5c1.5 -2.5 4 -4 6.5 -4a6 6 0 0 1 6 6m0 -3v6"/>
                                            </svg>
                                        </button>
                                    </form>
                                </template>
                                <template x-if="!payment.cashed_in">
                                    <form :action="'/payments/' + payment.id + '/cash-in'" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-icon btn-primary" title="{{ __('Cash In') }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10"/>
                                            </svg>
                                        </button>
                                    </form>
                                </template>
                                <template x-if="!payment.cashed_in">
                                    <form :action="'/payments/' + payment.id" method="POST">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-danger" title="{{ __('Delete') }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12"/><path d="M6 6l12 12"/>
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

    <!-- ==================== LOADING OVERLAY ==================== -->
    <template x-if="loading">
        <div class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
             style="background: rgba(0,0,0,0.1); z-index: 9999;">
            <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
        </div>
    </template>

    <!-- ==================== PAYMENT MODAL ==================== -->
    <template x-if="showModal">
        <div>
            <div class="payment-modal-overlay" @click="closeModal()"></div>
            <div class="payment-modal-container" @keydown.escape.window="closeModal()">
                <div class="payment-modal-box" @click.stop>

                    <div class="modal-head">
                        <h3>{{ __('Add Payment') }}</h3>
                        <button type="button" class="close-btn" @click="closeModal()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18"/><path d="M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="modal-divider"></div>

                    <div class="modal-content-area">
                        <div class="modal-errors p-3" x-show="Object.keys(modalErrors).length > 0" x-cloak>
                            <ul class="mb-0 ps-3">
                                <template x-for="(msgs, field) in modalErrors" :key="field">
                                    <template x-for="msg in msgs" :key="msg">
                                        <li x-text="msg"></li>
                                    </template>
                                </template>
                            </ul>
                        </div>

                        <div class="form-grid">
                            <div>
                                <label class="form-label">{{ __('Nature') }}</label>
                                <input type="text" class="form-control" x-model="form.nature"
                                       :class="{ 'is-invalid': modalErrors.nature }">
                            </div>
                            <div>
                                <label class="form-label">{{ __('Payment type') }}</label>
                                <select class="form-select" x-model="form.payment_type"
                                        :class="{ 'is-invalid': modalErrors.payment_type }">
                                    <option value="HandCash">Cash</option>
                                    <option value="Cheque">Cheque</option>
                                    <option value="Exchange">Lettre de change</option>
                                </select>
                            </div>
                            <div x-show="form.payment_type !== 'HandCash'">
                                <label class="form-label">{{ __('Bank') }}</label>
                                <select class="form-select" x-model="form.bank"
                                        :class="{ 'is-invalid': modalErrors.bank }">
                                    <option value="">{{ __('Select a bank:') }}</option>
                                    @foreach ($banks as $bank)
                                        <option value="{{ $bank->value }}">{{ $bank->value }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label">{{ __('Date') }}</label>
                                <input type="text" class="form-control" x-model="form.date"
                                       placeholder="dd/mm/yyyy" data-field="date"
                                       :class="{ 'is-invalid': modalErrors.date }"
                                       @input="formatDateField($event)">
                            </div>
                            <div>
                                <label class="form-label">{{ __('Echeance') }}</label>
                                <input type="text" class="form-control" x-model="form.echeance"
                                       placeholder="dd/mm/yyyy" data-field="echeance"
                                       :class="{ 'is-invalid': modalErrors.echeance }"
                                       @input="formatDateField($event)">
                            </div>
                            <div>
                                <label class="form-label">{{ __('Amount') }}</label>
                                <input type="number" class="form-control" x-model="form.amount"
                                       step="0.01" :class="{ 'is-invalid': modalErrors.amount }">
                            </div>
                            <div class="full-width">
                                <label class="form-label">{{ __('Description') }}</label>
                                <textarea class="form-control" x-model="form.description" rows="2"></textarea>
                            </div>
                        </div>

                        <div class="auto-alloc-hint p-2" x-show="modalOrderId" x-cloak>
                            {{ __('This payment will be automatically allocated to order') }}
                            <strong x-text="modalOrderInvoice"></strong>
                        </div>
                    </div>

                    <div class="modal-divider"></div>

                    <div class="modal-foot">
                        <button type="button" class="btn btn-cancel px-3 py-2" @click="closeModal()">{{ __('Cancel') }}</button>
                        <button type="button" class="btn btn-submit px-3 py-2" @click="submitPayment()" :disabled="modalSubmitting">
                            <span class="spinner-border spinner-border-sm me-1" x-show="modalSubmitting"></span>
                            {{ __('Add Payment') }}
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </template>
</div>
