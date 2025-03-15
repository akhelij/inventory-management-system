<?php

namespace App\Livewire\Tables;

use Carbon\Carbon;
use Livewire\Component;

class ProductHistory extends Component
{
    public $product;

    public $startDate;

    public $endDate;

    public $entries;

    public $totalIncoming = 0;

    public $totalOutgoing = 0;

    public function mount($product)
    {
        $this->product = $product;
        $this->startDate = Carbon::now()->subMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
        $this->loadEntries();
    }

    public function updatedStartDate()
    {
        $this->loadEntries();
    }

    public function updatedEndDate()
    {
        $this->loadEntries();
    }

    private function loadEntries()
    {
        $query = $this->product->activities()
            ->where('event', 'updated')
            ->whereRaw("JSON_EXTRACT(properties, '$.attributes.quantity') IS NOT NULL")
            ->when($this->startDate, function ($query) {
                return $query->whereDate('created_at', '>=', $this->startDate);
            })
            ->when($this->endDate, function ($query) {
                return $query->whereDate('created_at', '<=', $this->endDate);
            })
            ->orderBy('created_at', 'desc');

        // Reset totals
        $this->totalIncoming = 0;
        $this->totalOutgoing = 0;

        $this->entries = $query->get()->map(function ($activity) {
            $properties = json_decode($activity->properties);
            $oldQuantity = $properties->old->quantity ?? null;
            $newQuantity = $properties->attributes->quantity;
            $difference = $oldQuantity !== null ? $newQuantity - $oldQuantity : 0;

            // Update totals
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
