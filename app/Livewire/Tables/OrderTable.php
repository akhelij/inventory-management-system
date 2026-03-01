<?php

namespace App\Livewire\Tables;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
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

        try {
            $this->selectedOrder->update([
                'order_status' => $this->newStatus,
                'reason' => $this->statusReason,
            ]);

            $this->dispatch('orderStatusUpdated', [
                'message' => 'Order status has been updated successfully!',
            ]);

            $this->reset(['showWarningModal', 'selectedOrder', 'newStatus', 'statusReason', 'isOverLimit']);
        } catch (\Exception $e) {
            $this->dispatch('orderStatusError', [
                'message' => 'Error updating order status: '.$e->getMessage(),
            ]);
        }
    }

    public function forceApprove(): void
    {
        $this->updateOrderStatus(true);
    }

    public function cancelStatusUpdate(): void
    {
        $this->reset(['showWarningModal', 'selectedOrder', 'newStatus', 'statusReason', 'isOverLimit']);
    }

    public function render()
    {
        $query = Order::query();

        $query->when(auth()->user()->warehouse_id != null, fn ($q) => $q->where('user_id', auth()->id()));

        if (! auth()->user()->hasRole('admin')) {
            $query->where('user_id', auth()->id())
                ->orWhereIn('user_id', User::role('admin')->pluck('id'));
        }

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('order_date', [$this->startDate, $this->endDate]);
        }

        return view('livewire.tables.order-table', [
            'orders' => $query->with(['customer', 'details', 'user'])
                ->search($this->search)
                ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
                ->paginate($this->perPage),
        ]);
    }
}
