<?php

namespace App\Livewire\Tables;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class OrderTable extends Component
{
    use WithPagination;

    public int $perPage = 25;

    public string $search = '';

    public string $sortField = 'invoice_no';

    public bool $sortAsc = false;

    public ?string $startDate = null;

    public ?string $endDate = null;

    public ?string $customerType = null;

    public array $order_ids = [];

    public bool $showWarningModal = false;

    public ?Order $selectedOrder = null;

    public mixed $newStatus = null;

    public string $statusReason = '';

    public bool $isOverLimit = false;

    protected $listeners = ['refreshComponent' => '$refresh'];

    public function sortBy(string $field): void
    {
        $this->sortAsc = $this->sortField === $field ? ! $this->sortAsc : true;
        $this->sortField = $field;
    }

    public function selectOrder(int $orderId): void
    {
        if (in_array($orderId, $this->order_ids)) {
            $this->order_ids = array_diff($this->order_ids, [$orderId]);
        } else {
            $this->order_ids[] = $orderId;
        }
    }

    public function initiateStatusUpdate(int $orderId, mixed $newStatus): void
    {
        $this->selectedOrder = Order::find($orderId);
        $this->newStatus = $newStatus;

        if ($newStatus == OrderStatus::APPROVED) {
            $customer = Customer::find($this->selectedOrder->customer_id);
            $this->isOverLimit = $customer->is_out_of_limit;

            if ($this->isOverLimit) {
                $this->showWarningModal = true;

                return;
            }
        }

        $this->updateOrderStatus();
    }

    public function updateOrderStatus(bool $force = false): void
    {
        if (! $this->selectedOrder) {
            return;
        }

        if ($this->isOverLimit && ! $force) {
            $this->showWarningModal = true;

            return;
        }

        if ($this->newStatus == OrderStatus::APPROVED) {
            $stockCheck = app(StockService::class)->canApproveOrder($this->selectedOrder);

            if (! $stockCheck['can_approve']) {
                $this->dispatch('orderStatusError', [
                    'message' => __('Cannot approve order due to insufficient stock: ').implode(', ', $stockCheck['issues']),
                ]);

                $this->reset(['showWarningModal', 'selectedOrder', 'newStatus', 'statusReason', 'isOverLimit']);

                return;
            }
        }

        try {
            // The transaction guarantees the status change rolls back when the
            // observer's stock deduction fails — the status must never be
            // persisted without its stock side effects.
            DB::transaction(function (): void {
                $this->selectedOrder->update([
                    'order_status' => $this->newStatus,
                    'reason' => $this->statusReason,
                ]);
            });

            $this->dispatch('orderStatusUpdated', [
                'message' => __('Order status has been updated successfully!'),
            ]);
        } catch (\Exception $e) {
            $this->dispatch('orderStatusError', [
                'message' => __('Error updating order status: ').$e->getMessage(),
            ]);
        }

        $this->reset(['showWarningModal', 'selectedOrder', 'newStatus', 'statusReason', 'isOverLimit']);
    }

    public function forceApprove(): void
    {
        $this->updateOrderStatus(true);
    }

    public function cancelStatusUpdate(): void
    {
        $this->reset(['showWarningModal', 'selectedOrder', 'newStatus', 'statusReason', 'isOverLimit']);
    }

    public function setCustomerType(?string $type): void
    {
        $this->customerType = $this->customerType === $type ? null : $type;
        $this->resetPage();
    }

    public function render()
    {
        $query = Order::query();

        if ($this->startDate && $this->endDate) {
            try {
                $start = \Carbon\Carbon::createFromFormat('d/m/Y', $this->startDate)->startOfDay();
                $end = \Carbon\Carbon::createFromFormat('d/m/Y', $this->endDate)->endOfDay();
                $query->whereBetween('order_date', [$start, $end]);
            } catch (\Exception) {
                // Fallback: try Y-m-d format for backward compatibility
                $query->whereBetween('order_date', [$this->startDate, $this->endDate]);
            }
        }

        if ($this->customerType) {
            $query->whereHas('customer', fn ($q) => $q->where('category', $this->customerType));
        }

        return view('livewire.tables.order-table', [
            'orders' => $query->with(['customer', 'details', 'user'])
                ->search($this->search)
                ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
                ->paginate($this->perPage),
        ]);
    }
}
