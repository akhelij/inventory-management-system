<?php

namespace App\Livewire\Tables;

use Carbon\Carbon;
use Livewire\Component;

class ProductHistory extends Component
{
    public $product;

    public string $startDate;

    public string $endDate;

    public $entries;

    public int $totalIncoming = 0;

    public int $totalOutgoing = 0;

    public function mount($product): void
    {
        $this->product = $product;
        $this->startDate = Carbon::now()->subMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
        $this->loadEntries();
    }

    public function updatedStartDate(): void
    {
        $this->loadEntries();
    }

    public function updatedEndDate(): void
    {
        $this->loadEntries();
    }

    private function loadEntries(): void
    {
        $this->totalIncoming = 0;
        $this->totalOutgoing = 0;

        $this->entries = $this->product->activities()
            ->where('event', 'updated')
            ->whereRaw("JSON_EXTRACT(properties, '$.attributes.quantity') IS NOT NULL")
            ->when($this->startDate, fn ($query) => $query->whereDate('created_at', '>=', $this->startDate))
            ->when($this->endDate, fn ($query) => $query->whereDate('created_at', '<=', $this->endDate))
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($activity) {
                $properties = json_decode($activity->properties);
                $oldQuantity = $properties->old->quantity ?? null;
                $newQuantity = $properties->attributes->quantity;
                $difference = $oldQuantity !== null ? $newQuantity - $oldQuantity : 0;

                if ($difference > 0) {
                    $this->totalIncoming += $difference;
                } else {
                    $this->totalOutgoing += abs($difference);
                }

                return [
                    'date' => $activity->created_at->format('Y-m-d H:i:s'),
                    'old_quantity' => $oldQuantity,
                    'new_quantity' => $newQuantity,
                    'difference' => $difference,
                    'user' => $activity->causer->name ?? 'Unknown',
                ];
            });
    }

    public function render()
    {
        return view('livewire.tables.product-history');
    }
}
