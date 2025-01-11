<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;
use App\Enums\OrderStatus;
use Carbon\Carbon;

class ProfileStats extends Component
{
    public $user;
    public $startDate;
    public $endDate;
    public $sales;
    public $totalSales = 0;
    public $totalAmount = 0;
    public $approvedSales = 0;
    public $pendingSales = 0;
    public $canceledSales = 0;

    public function mount($user)
    {
        $this->user = $user;
        $this->startDate = Carbon::now()->subYear()->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
        $this->loadSales();
    }

    public function updatedStartDate()
    {
        $this->loadSales();
    }

    public function updatedEndDate()
    {
        $this->loadSales();
    }

    private function loadSales()
    {
        $query = $this->user->orders()
            ->when($this->startDate, function($query) {
                return $query->whereDate('order_date', '>=', $this->startDate);
            })
            ->when($this->endDate, function($query) {
                return $query->whereDate('order_date', '<=', $this->endDate);
            })
            ->orderBy('order_date', 'desc');

        // Reset all counters
        $this->totalSales = 0;
        $this->totalAmount = 0;
        $this->approvedSales = 0;
        $this->pendingSales = 0;
        $this->canceledSales = 0;

        $this->sales = $query->get()->map(function ($order) {
            // Update counters based on order status
            $this->totalSales++;

            switch ($order->order_status) {
                case OrderStatus::APPROVED:
                    $this->approvedSales++;
                    $this->totalAmount += $order->total;
                    break;
                case OrderStatus::PENDING:
                    $this->pendingSales++;
                    break;
                case OrderStatus::CANCELED:
                    $this->canceledSales++;
                    break;
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
            ]
        ]);
    }
}
