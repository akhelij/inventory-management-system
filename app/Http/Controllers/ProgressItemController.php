<?php

namespace App\Http\Controllers;

use App\Models\ProgressItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class ProgressItemController extends Controller
{
    /**
     * Constructor to apply middleware
     */
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * Check if user is authorized
     */
    private function checkAuthorization()
    {
        if (!Gate::allows('manage-progress-items')) {
            abort(403, 'Unauthorized action.');
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->checkAuthorization();
        
        $progressItems = ProgressItem::orderBy('created_at', 'desc')->get();
        $totalUnpaid = ProgressItem::where('payment_status', '!=', 'paid')->sum(DB::raw('price - amount_paid'));

        return view('progress_items.index', compact('progressItems', 'totalUnpaid'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->checkAuthorization();
        
        return view('progress_items.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->checkAuthorization();
        
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

        return redirect()->route('progress-items.index')
            ->with('success', 'Progress item created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProgressItem $progressItem)
    {
        $this->checkAuthorization();
        
        return view('progress_items.edit', compact('progressItem'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProgressItem $progressItem)
    {
        $this->checkAuthorization();
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:not_started,in_progress,completed',
            'amount_paid' => 'nullable|numeric|min:0',
            'is_visible' => 'boolean',
        ]);

        // Update item details
        $progressItem->update($validated);
        
        // Update payment status based on amount paid
        $progressItem->updatePaymentStatus();

        return redirect()->route('progress-items.index')
            ->with('success', 'Progress item updated successfully.');
    }

    /**
     * Record a payment for the progress item.
     */
    public function recordPayment(Request $request, ProgressItem $progressItem)
    {
        $this->checkAuthorization();
        
        $validated = $request->validate([
            'payment_amount' => 'required|numeric|min:0.01|max:' . $progressItem->remaining_amount,
        ]);

        $progressItem->amount_paid += $validated['payment_amount'];
        $progressItem->updatePaymentStatus();

        return redirect()->route('progress-items.index')
            ->with('success', 'Payment recorded successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProgressItem $progressItem)
    {
        $this->checkAuthorization();
        
        $progressItem->delete();

        return redirect()->route('progress-items.index')
            ->with('success', 'Progress item deleted successfully.');
    }
}
