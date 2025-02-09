<?php

namespace App\Http\Controllers;

use App\Http\Requests\RepairTicket\StoreRequest;
use App\Http\Requests\RepairTicket\UpdateRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\RepairTicket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class RepairTicketController extends Controller
{
    public function index()
    {
        $tickets = RepairTicket::with(['customer', 'product', 'technician'])
            ->latest()
            ->paginate(10);

        return view('repair-tickets.index', compact('tickets'));
    }

    public function create()
    {
        $customers = Customer::all();
        $products = Product::all();
        $technicians = User::where('role', 'technician')->get();

        return view('repair-tickets.create', compact('customers', 'products', 'technicians'));
    }

    public function store(StoreRequest $request)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $request) {
            // Generate ticket number
            $ticketNumber = 'RT-' . date('Ymd') . '-' . str_pad(random_int(1, 999), 3, '0', STR_PAD_LEFT);

            // Create repair ticket
            $ticket = RepairTicket::create([
                'ticket_number' => $ticketNumber,
                'customer_id' => $validated['customer_id'],
                'product_id' => $validated['product_id'],
                'created_by' => auth()->id(),
                'technician_id' => $validated['technician_id'] ?? null,
                'serial_number' => $validated['serial_number'] ?? null,
                'problem_description' => $validated['problem_description'],
                'status' => 'RECEIVED'
            ]);

            // Handle photo uploads
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $path = $photo->store('repair-photos', 'public');
                    $ticket->photos()->create(['photo_path' => $path]);
                }
            }

            return $ticket;
        });

        return redirect()
            ->route('repair-tickets.index')
            ->with('success', 'Repair ticket created successfully');
    }

    public function updateStatus(RepairTicket $repairTicket, UpdateRequest $request)
    {
        $validated = $request->validated();

        // Status will be tracked by observer
        $repairTicket->update([
            'status' => $validated['status'],
            'technician_id' => $validated['technician_id'] ?? $repairTicket->technician_id
        ]);

        return redirect()
            ->back()
            ->with('success', 'Status updated successfully');
    }
}
