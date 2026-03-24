<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ __('Orders') }}</h3>
            <div class="card-actions d-flex align-items-center gap-2">
                <input type="date" class="form-control form-control-sm" wire:model.live="startDate" style="width: 140px;">
                <span class="text-muted">{{ __('to') }}</span>
                <input type="date" class="form-control form-control-sm" wire:model.live="endDate" style="width: 140px;">
            </div>
        </div>

        <div class="card-body py-2 border-bottom">
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn {{ $statusFilter === null ? 'btn-primary' : 'btn-outline-primary' }}"
                    wire:click="setStatusFilter(null)">
                    {{ __('All') }}
                </button>
                <button type="button" class="btn {{ $statusFilter === 'approved' ? 'btn-success' : 'btn-outline-success' }}"
                    wire:click="setStatusFilter('approved')">
                    {{ __('Approved') }}
                </button>
                <button type="button" class="btn {{ $statusFilter === 'pending' ? 'btn-warning' : 'btn-outline-warning' }}"
                    wire:click="setStatusFilter('pending')">
                    {{ __('Pending') }}
                </button>
                <button type="button" class="btn {{ $statusFilter === 'canceled' ? 'btn-danger' : 'btn-outline-danger' }}"
                    wire:click="setStatusFilter('canceled')">
                    {{ __('Canceled') }}
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>{{ __('Invoice No.') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th class="text-end">{{ __('Total') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-end">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>
                                <a href="{{ route('orders.show', $order->uuid) }}" class="text-reset">
                                    {{ $order->invoice_no }}
                                </a>
                            </td>
                            <td>{{ $order->customer?->name ?? '-' }}</td>
                            <td>{{ $order->order_date->format('d/m/Y') }}</td>
                            <td class="text-end">{{ Number::currency($order->total, 'MAD') }}</td>
                            <td>
                                @if ($order->order_status === \App\Enums\OrderStatus::APPROVED)
                                    <span class="badge bg-success">{{ __('Approved') }}</span>
                                @elseif ($order->order_status === \App\Enums\OrderStatus::PENDING)
                                    <span class="badge bg-warning">{{ __('Pending') }}</span>
                                @elseif ($order->order_status === \App\Enums\OrderStatus::CANCELED)
                                    <span class="badge bg-danger">{{ __('Canceled') }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('orders.show', $order->uuid) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i>{{ __('View') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                {{ __('No orders found') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex align-items-center">
            {{ $orders->links() }}
        </div>
    </div>
</div>
