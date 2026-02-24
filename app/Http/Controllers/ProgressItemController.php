<?php

namespace App\Http\Controllers;

use App\Models\ProgressItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProgressItemController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index(): View
    {
        Gate::authorize('manage-progress-items');

        return view('progress_items.index', [
            'progressItems' => ProgressItem::latest()->get(),
            'totalUnpaid' => ProgressItem::where('payment_status', '!=', 'paid')->sum(DB::raw('price - amount_paid')),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('manage-progress-items');

        return view('progress_items.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage-progress-items');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:not_started,in_progress,completed',
            'amount_paid' => 'nullable|numeric|min:0',
        ]);

        $progressItem = ProgressItem::create($validated);

        if (isset($validated['amount_paid']) && $validated['amount_paid'] > 0) {
            $progressItem->amount_paid = $validated['amount_paid'];
            $progressItem->updatePaymentStatus();
        }

        return to_route('progress-items.index')->with('success', 'Progress item created successfully.');
    }

    public function edit(ProgressItem $progressItem): View
    {
        Gate::authorize('manage-progress-items');

        return view('progress_items.edit', compact('progressItem'));
    }

    public function update(Request $request, ProgressItem $progressItem): RedirectResponse
    {
        Gate::authorize('manage-progress-items');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:not_started,in_progress,completed',
            'amount_paid' => 'nullable|numeric|min:0',
            'is_visible' => 'boolean',
        ]);

        $progressItem->update($validated);
        $progressItem->updatePaymentStatus();

        return to_route('progress-items.index')->with('success', 'Progress item updated successfully.');
    }

    public function recordPayment(Request $request, ProgressItem $progressItem): RedirectResponse
    {
        Gate::authorize('manage-progress-items');

        $validated = $request->validate([
            'payment_amount' => 'required|numeric|min:0.01|max:'.$progressItem->remaining_amount,
        ]);

        $progressItem->amount_paid += $validated['payment_amount'];
        $progressItem->updatePaymentStatus();

        return to_route('progress-items.index')->with('success', 'Payment recorded successfully.');
    }

    public function destroy(ProgressItem $progressItem): RedirectResponse
    {
        Gate::authorize('manage-progress-items');

        $progressItem->delete();

        return to_route('progress-items.index')->with('success', 'Progress item deleted successfully.');
    }
}
