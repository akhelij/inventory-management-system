<?php

namespace App\Livewire;

use App\Enums\OrderStatus;
use Carbon\Carbon;
use Livewire\Component;

class ProfileStats extends Component
{
    public $user;

    public string $startDate;

    public string $endDate;

    public $sales;

    public int $totalSales = 0;

    public float $totalAmount = 0;

    public int $approvedSales = 0;

    public int $pendingSales = 0;

    public int $canceledSales = 0;

    public function mount($user): void
    {
        $this->user = $user;
        $this->startDate = Carbon::now()->subYear()->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
        $this->loadSales();
    }

    public function updatedStartDate(): void
    {
        $this->loadSales();
    }

    public function updatedEndDate(): void
    {
        $this->loadSales();
    }

    private function loadSales(): void
    {
        $this->totalSales = 0;
        $this->totalAmount = 0;
        $this->approvedSales = 0;
        $this->pendingSales = 0;
        $this->canceledSales = 0;

        $this->sales = $this->user->orders()
            ->when($this->startDate, fn ($query) => $query->whereDate('order_date', '>=', $this->startDate))
            ->when($this->endDate, fn ($query) => $query->whereDate('order_date', '<=', $this->endDate))
            ->orderBy('order_date', 'desc')
            ->get()
            ->map(function ($order) {
                $this->totalSales++;

                if ($order->order_status === OrderStatus::APPROVED) {
                    $this->approvedSales++;
                    $this->totalAmount += $order->total;
                } elseif ($order->order_status === OrderStatus::PENDING) {
                    $this->pendingSales++;
                } elseif ($order->order_status === OrderStatus::CANCELED) {
                    $this->canceledSales++;
                }

                return [
                    'date' => $order->order_date->format('Y-m-d'),
                    'status' => $order->status,
                    'total' => $order->total,
                    'invoice_no' => $order->invoice_no,
                ];
            });
    }

    public function render()
    {
        return view('livewire.profile-stats', [
            'stats' => [
                'totalSales' => $this->totalSales,
                'totalAmount' => $this->totalAmount,
                'approvedSales' => $this->approvedSales,
                'pendingSales' => $this->pendingSales,
                'canceledSales' => $this->canceledSales,
            ],
        ]);
    }
}
