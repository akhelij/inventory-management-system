<?php

namespace App\Livewire\Tables;

use App\Models\Order;
use App\Models\User;
use App\Models\Customer;
use App\Enums\OrderStatus;
use Livewire\Component;
use Livewire\WithPagination;

class OrderTable extends Component
{
    use WithPagination;

    public $perPage = 25;
    public $search = '';
    public $sortField = 'invoice_no';
    public $sortAsc = false;
    public $startDate = null;
    public $endDate = null;
    public $order_ids = [];

    // New properties for status update
    public $showWarningModal = false;
    public $selectedOrder = null;
    public $newStatus = null;
    public $statusReason = '';
    public $isOverLimit = false;

    protected $listeners = ['refreshComponent' => '$refresh'];

    public function sortBy($field): void
    {
        if ($this->sortField === $field) {
            $this->sortAsc = !$this->sortAsc;
        } else {
            $this->sortAsc = true;
        }
        $this->sortField = $field;
    }

    public function selectOrder($orderId)
    {
        if (in_array($orderId, $this->order_ids)) {
            $this->order_ids = array_diff($this->order_ids, [$orderId]);
        } else {
            $this->order_ids[] = $orderId;
        }
    }

    public function initiateStatusUpdate($orderId, $newStatus)
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

    public function updateOrderStatus($force = false)
    {
        if (!$this->selectedOrder) {
            return;
        }

        if ($this->isOverLimit && !$force) {
            $this->showWarningModal = true;
            return;
        }

        try {
            $this->selectedOrder->update([
                'order_status' => $this->newStatus,
                'reason' => $this->statusReason
            ]);

            $this->dispatch('orderStatusUpdated', [
                'message' => 'Order status has been updated successfully!'
            ]);

            $this->reset(['showWarningModal', 'selectedOrder', 'newStatus', 'statusReason', 'isOverLimit']);
        } catch (\Exception $e) {
            $this->dispatch('orderStatusError', [
                'message' => 'Error updating order status: ' . $e->getMessage()
            ]);
        }
    }

    public function forceApprove()
    {
        $this->updateOrderStatus(true);
    }

    public function cancelStatusUpdate()
    {
        $this->reset(['showWarningModal', 'selectedOrder', 'newStatus', 'statusReason', 'isOverLimit']);
    }

    public function render()
    {
        $query = Order::query();
        if (!auth()->user()->hasRole('admin')) {
            $query->where("user_id", auth()->id())
                ->orWhereIn("user_id", User::role('admin')->pluck('id'));
        }

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('order_date', [$this->startDate, $this->endDate]);
        }

        return view('livewire.tables.order-table', [
            'orders' => $query->with(['customer', 'details'])
                ->search($this->search)
                ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
                ->paginate($this->perPage)
        ]);
    }
}
