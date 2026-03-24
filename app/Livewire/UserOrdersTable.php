<?php

namespace App\Livewire;

use App\Enums\OrderStatus;
use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;

class UserOrdersTable extends Component
{
    use WithPagination;

    public int $userId;

    public ?string $statusFilter = null;

    public string $startDate;

    public string $endDate;

    public int $perPage = 15;

    public function mount(int $userId): void
    {
        $this->userId = $userId;
        $this->startDate = now()->subYear()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function setStatusFilter(?string $status): void
    {
        $this->statusFilter = $this->statusFilter === $status ? null : $status;
        $this->resetPage();
    }

    public function updatedStartDate(): void
    {
        $this->resetPage();
    }

    public function updatedEndDate(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Order::where('user_id', $this->userId)
            ->when($this->startDate, fn ($q) => $q->whereDate('order_date', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('order_date', '<=', $this->endDate));

        if ($this->statusFilter !== null) {
            $query->where('order_status', match ($this->statusFilter) {
                'approved' => OrderStatus::APPROVED,
                'pending' => OrderStatus::PENDING,
                'canceled' => OrderStatus::CANCELED,
            });
        }

        return view('livewire.user-orders-table', [
            'orders' => $query->with('customer')
                ->orderBy('order_date', 'desc')
                ->paginate($this->perPage),
        ]);
    }
}
