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

    /* Cheque preview popup on hover */
    .cheque-preview-trigger {
        display: inline-block;
    }
    .cheque-preview-popup {
        display: none;
        position: absolute;
        bottom: calc(100% + 8px);
        left: 50%;
        transform: translateX(-50%);
        z-index: 1050;
        background: #fff;
        border-radius: 8px;
        padding: 8px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        white-space: nowrap;
    }
    .cheque-preview-trigger:hover .cheque-preview-popup {
        display: block;
    }
    .cheque-preview-popup::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border: 6px solid transparent;
        border-top-color: #fff;
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
        'bank' => $payment->bank,
        'date' => $payment->date,
        'echeance' => $payment->echeance,
        'amount' => $payment->amount,
        'cashed_in' => (bool) $payment->cashed_in,
        'reported' => (bool) $payment->reported,
        'cheque_photo' => $payment->cheque_photo,
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
    chequeFile: null,
    chequePreview: null,
    chequeScanning: false,
    chequeError: null,
    chequeSuccess: false,
    activePaymentMenu: null,
    menuStyle: { top: '0px', left: '0px' },
    chequeUploadModal: false,
    chequeUploadPayment: null,
    chequeUploadFile: null,
    chequeUploadPreview: null,
    chequeUploadSubmitting: false,
    chequeUploadError: null,
    form: {
        nature: '',
        payment_type: 'HandCash',
        bank: '',
        date: new Date().toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' }),
        echeance: '',
        amount: '',
        description: '',
        cheque_photo: '',
    },

    async scanCheque() {
        if (!this.chequeFile) return;
        this.chequeScanning = true;
        this.chequeError = null;
        this.chequeSuccess = false;
        this.chequePreview = URL.createObjectURL(this.chequeFile);

        const formData = new FormData();
        formData.append('cheque_image', this.chequeFile);

        try {
            const res = await fetch('/api/cheque-scan', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                },
                body: formData,
            });
            const data = await res.json();
            if (data.success) {
                const d = data.data;
                if (d.nature) this.form.nature = d.nature;
                if (d.amount) this.form.amount = d.amount;
                if (d.echeance) this.form.echeance = d.echeance;
                if (d.bank) this.form.bank = d.bank;
                if (data.cheque_photo) this.form.cheque_photo = data.cheque_photo;
                this.chequeSuccess = true;
            } else {
                this.chequeError = data.error || 'Failed to scan cheque.';
            }
        } catch (e) {
            this.chequeError = 'Failed to process cheque image.';
        }
        this.chequeScanning = false;
    },

    getActivePayment() {
        return this.payments.find(p => p.id === this.activePaymentMenu);
    },

    positionPaymentMenu(btn) {
        const rect = btn.getBoundingClientRect();
        const menuH = 180;
        const spaceBelow = window.innerHeight - rect.bottom;
        const top = spaceBelow < menuH ? rect.top - menuH : rect.bottom + 4;
        this.menuStyle = {
            top: top + 'px',
            left: (rect.right - 210) + 'px',
        };
    },

    formatCurrency(amount) {
        return new Intl.NumberFormat('fr-MA', { style: 'currency', currency: 'MAD', minimumFractionDigits: 2 }).format(amount);
    },

    openChequeUpload(payment) {
        this.chequeUploadPayment = payment;
        this.chequeUploadFile = null;
        this.chequeUploadPreview = null;
        this.chequeUploadSubmitting = false;
        this.chequeUploadError = null;
        this.chequeUploadModal = true;
    },

    async submitChequeUpload() {
        if (!this.chequeUploadFile || !this.chequeUploadPayment) return;
        this.chequeUploadSubmitting = true;
        this.chequeUploadError = null;

        const formData = new FormData();
        formData.append('cheque_image', this.chequeUploadFile);

        try {
            const res = await fetch('/api/payments/' + this.chequeUploadPayment.id + '/attach-cheque', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                },
                body: formData,
            });
            const data = await res.json();
            if (res.ok) {
                this.chequeUploadPayment.cheque_photo = data.cheque_photo;
                if (data.bank) this.chequeUploadPayment.bank = data.bank;
                this.chequeUploadModal = false;
            } else {
                this.chequeUploadError = data.message || 'Failed to upload cheque.';
            }
        } catch (e) {
            this.chequeUploadError = 'Failed to upload cheque photo.';
        }
        this.chequeUploadSubmitting = false;
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
            cheque_photo: '',
        };
        this.modalOrderId = order ? order.id : null;
        this.modalOrderInvoice = order ? order.invoice_no : '';
        this.modalSubmitting = false;
        this.chequeFile = null;
        this.chequePreview = null;
        this.chequeError = null;
        this.chequeSuccess = false;
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
            cheque_photo: this.form.cheque_photo || null,
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
                const row = this.$el.querySelector('.col-7 tbody tr');
                if (row) { row.classList.add('card-animate'); setTimeout(() => row.classList.remove('card-animate'), 400); }
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
            <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                <table class="table table-vcenter table-nowrap">
                    <thead>
                        <tr>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Nature') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Echeance') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="payment in payments" :key="payment.id">
                            <tr :class="{ 'opacity-50': payment.is_fully_allocated, 'table-primary': payment.dragging }"
                                :draggable="allocationEnabled && !payment.is_fully_allocated && !loading"
                                @dragstart="handleDragStart($event, payment)"
                                @dragend="handleDragEnd(payment)"
                                :style="allocationEnabled && !payment.is_fully_allocated ? 'cursor: grab;' : ''">
                                <td x-text="payment.date"></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-bold" x-text="payment.nature"></span>
                                        <template x-if="payment.cheque_photo">
                                            <span class="position-relative cheque-preview-trigger" style="cursor: pointer;">
                                                <i class="fas fa-image text-primary"></i>
                                                <div class="cheque-preview-popup">
                                                    <img :src="'/storage/' + payment.cheque_photo" alt="Cheque" style="max-width: 300px; max-height: 200px; border-radius: 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                                                    <template x-if="payment.bank">
                                                        <div class="mt-1 text-center">
                                                            <span class="badge bg-blue-lt" x-text="payment.bank"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </span>
                                        </template>
                                        <template x-if="payment.bank && !payment.cheque_photo">
                                            <span class="badge bg-blue-lt" x-text="payment.bank" style="font-size: 0.7rem;"></span>
                                        </template>
                                    </div>
                                    <template x-if="payment.amount > 0 && payment.unallocated_amount < payment.amount">
                                        <div class="mt-1">
                                            <div class="progress progress-sm" style="min-width: 80px;">
                                                <div class="progress-bar bg-success"
                                                     :style="'width: ' + ((payment.amount - payment.unallocated_amount) / payment.amount * 100) + '%'"></div>
                                            </div>
                                            <small class="text-success" x-show="payment.is_fully_allocated">{{ __('Fully Allocated') }}</small>
                                            <small class="text-info" x-show="!payment.is_fully_allocated"
                                                   x-text="'{{ __('Available') }}: ' + formatCurrency(payment.unallocated_amount)"></small>
                                        </div>
                                    </template>
                                </td>
                                <td x-text="payment.payment_type"></td>
                                <td class="fw-bold" x-text="formatCurrency(payment.amount)"></td>
                                <td x-text="payment.echeance || '—'"></td>
                                <td>
                                    <template x-if="payment.reported">
                                        <x-status dot color="red">{{ __('Reported') }}</x-status>
                                    </template>
                                    <template x-if="payment.cashed_in && !payment.reported">
                                        <x-status dot color="green">{{ __('Cashed In') }}</x-status>
                                    </template>
                                    <template x-if="!payment.cashed_in && !payment.reported">
                                        <x-status dot color="orange">{{ __('Pending') }}</x-status>
                                    </template>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-ghost-secondary btn-icon"
                                            @click="activePaymentMenu = activePaymentMenu === payment.id ? null : payment.id; if(activePaymentMenu === payment.id) { $nextTick(() => { positionPaymentMenu($event.target.closest('button')) }) }">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ==================== PAYMENT ACTIONS DROPDOWN (fixed position, outside table) ==================== -->
    <div x-show="activePaymentMenu" x-cloak>
        <div class="position-fixed top-0 start-0 w-100 h-100" style="z-index: 1050;" @click="activePaymentMenu = null"></div>
        <div class="payment-actions-floating" x-bind:style="'position:fixed; z-index:1060; top:' + menuStyle.top + '; left:' + menuStyle.left + ';'">
            <div class="dropdown-menu show" style="min-width: 210px; box-shadow: 0 8px 24px rgba(0,0,0,0.15);">
                <template x-if="getActivePayment() && !getActivePayment().reported && !getActivePayment().cashed_in">
                    <form class="reportForm" :action="'/payments/' + activePaymentMenu + '/report'" method="POST">
                        @csrf
                        <button class="dropdown-item text-warning" type="submit">
                            <i class="fas fa-flag me-2"></i>{{ __('Reporté') }}
                        </button>
                    </form>
                </template>
                <template x-if="getActivePayment() && !getActivePayment().cashed_in">
                    <form :action="'/payments/' + activePaymentMenu + '/cash-in'" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item text-primary">
                            <i class="fas fa-money-bill-wave me-2"></i>{{ __('Encaisser') }}
                        </button>
                    </form>
                </template>
                <template x-if="getActivePayment() && (getActivePayment().payment_type === 'Cheque' || getActivePayment().payment_type === 'Exchange')">
                    <button type="button" class="dropdown-item text-info" @click="openChequeUpload(getActivePayment()); activePaymentMenu = null;">
                        <i class="fas fa-camera me-2"></i><span x-text="getActivePayment()?.cheque_photo ? '{{ __('Update Cheque Photo') }}' : '{{ __('Attach Cheque Photo') }}'"></span>
                    </button>
                </template>
                <template x-if="getActivePayment() && !getActivePayment().cashed_in">
                    <div>
                        <div class="dropdown-divider"></div>
                        <form :action="'/payments/' + activePaymentMenu" method="POST"
                              @submit.prevent="if(confirm('{{ __('Are you sure?') }}')) $el.submit()">
                            @csrf @method('DELETE')
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="fas fa-trash me-2"></i>{{ __('Delete') }}
                            </button>
                        </form>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- ==================== CHEQUE UPLOAD MODAL ==================== -->
    <div x-show="chequeUploadModal" x-cloak style="position: fixed; inset: 0; z-index: 9998;">
        <div style="position: fixed; inset: 0; background: rgba(107,114,128,0.5); backdrop-filter: blur(2px);" @click="chequeUploadModal = false"></div>
        <div style="position: fixed; inset: 0; display: flex; align-items: center; justify-content: center; z-index: 9999;">
            <div class="card shadow-lg" style="width: 440px; max-width: 90vw; animation: slideIn 0.2s ease-out;" @click.stop>
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-camera me-2"></i>{{ __('Attach Cheque Photo') }}</h3>
                    <div class="card-actions">
                        <button class="btn btn-ghost-secondary btn-icon btn-sm" @click="chequeUploadModal = false">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18"/><path d="M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        {{ __('Upload a cheque photo for payment') }} <strong x-text="chequeUploadPayment?.nature"></strong>
                    </p>
                    <input type="file" accept="image/*" capture="environment" class="form-control mb-3"
                           @change="chequeUploadFile = $event.target.files[0]; chequeUploadPreview = chequeUploadFile ? URL.createObjectURL(chequeUploadFile) : null">
                    <template x-if="chequeUploadPreview">
                        <img :src="chequeUploadPreview" alt="Cheque" class="img-fluid rounded mb-3" style="max-height: 160px;">
                    </template>
                    <template x-if="chequeUploadError">
                        <div class="alert alert-danger py-2 mb-3 small" x-text="chequeUploadError"></div>
                    </template>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <button class="btn btn-ghost-secondary" @click="chequeUploadModal = false">{{ __('Cancel') }}</button>
                    <button class="btn btn-primary" :disabled="!chequeUploadFile || chequeUploadSubmitting" @click="submitChequeUpload()">
                        <span x-show="!chequeUploadSubmitting"><i class="fas fa-upload me-1"></i>{{ __('Upload & Scan') }}</span>
                        <span x-show="chequeUploadSubmitting"><span class="spinner-border spinner-border-sm me-1"></span>{{ __('Uploading...') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== LOADING OVERLAY ==================== -->
    <template x-if="loading">
        <div class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
             style="background: rgba(0,0,0,0.1); z-index: 9999;">
            <div class="spinner-border text-primary" role="status"><span class="visually-hidden">{{ __('Loading...') }}</span></div>
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

                        {{-- Cheque Scanner (plain HTML, no Livewire) --}}
                        <div x-show="form.payment_type === 'Cheque'" x-cloak class="mb-3 p-3" style="background: #f8fafc; border-radius: 8px; border: 1px dashed #cbd5e1;">
                            <label class="form-label fw-bold mb-2"><i class="fas fa-money-check me-1"></i>{{ __('Scan Cheque') }}</label>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <input type="file" accept="image/*" capture="environment" class="form-control form-control-sm flex-grow-1"
                                       @change="chequeFile = $event.target.files[0]">
                                <button type="button" class="btn btn-sm btn-primary flex-shrink-0"
                                        x-show="chequeFile" x-cloak
                                        :disabled="chequeScanning"
                                        @click="scanCheque()">
                                    <span x-show="!chequeScanning"><i class="fas fa-search me-1"></i>{{ __('Scan') }}</span>
                                    <span x-show="chequeScanning"><span class="spinner-border spinner-border-sm me-1"></span>{{ __('Processing...') }}</span>
                                </button>
                            </div>
                            <p class="text-muted small mb-2"><i class="fas fa-info-circle me-1"></i>{{ __('Upload a cheque photo then click Scan to auto-fill.') }}</p>
                            <template x-if="chequePreview">
                                <img :src="chequePreview" alt="Cheque" class="img-fluid rounded mb-2" style="max-height: 120px;">
                            </template>
                            <template x-if="chequeError">
                                <div class="alert alert-warning py-1 px-2 mb-0 small" x-text="chequeError"></div>
                            </template>
                            <template x-if="chequeSuccess">
                                <div class="alert alert-success py-1 px-2 mb-0 small"><i class="fas fa-check me-1"></i>{{ __('Cheque data extracted!') }}</div>
                            </template>
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
                                    <option value="HandCash">{{ __('Cash') }}</option>
                                    <option value="Cheque">{{ __('Cheque') }}</option>
                                    <option value="Exchange">{{ __('Lettre de change') }}</option>
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
