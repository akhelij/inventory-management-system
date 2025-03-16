<?php

namespace App\Http\Controllers;

use App\Http\Requests\RepairTicket\StoreRequest;
use App\Http\Requests\RepairTicket\UpdateRequest;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Product;
use App\Models\RepairTicket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
        $technicians = User::role('technicien')->get();
        $drivers = Driver::all();

        return view('repair-tickets.create', compact('customers', 'drivers', 'products', 'technicians'));
    }

    public function store(StoreRequest $request)
    {
        DB::beginTransaction();
        try {
            // Generate ticket number (you might want to customize this format)
            $ticketNumber = $request->ticket_number ?? 'RT-'.date('Ymd').'-'.str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);

            // Create repair ticket
            $repairTicket = RepairTicket::create([
                'ticket_number' => $ticketNumber,
                'customer_id' => $request->brought_by === 'customer' ? $request->customer_id : null,
                'driver_id' => $request->brought_by === 'driver' ? $request->driver_id : null,
                'brought_by' => $request->brought_by,
                'product_id' => $request->product_id,
                'created_by' => auth()->id(),
                'technician_id' => $request->technician_id,
                'serial_number' => $request->serial_number,
                'problem_description' => $request->problem_description,
                'status' => 'RECEIVED',
            ]);

            // Handle photo uploads
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $filename = time() . '_' . $photo->getClientOriginalName();
                    $uploadPath = public_path('storage/repair-photos');

                    // Create directory if it doesn't exist
                    if (!file_exists($uploadPath)) {
                        mkdir($uploadPath, 0777, true);
                    }

                    // Move the file
                    $photo->move($uploadPath, $filename);
                    $path = 'repair-photos/' . $filename;
                    $repairTicket->photos()->create([
                        'photo_path' => $path,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('repair-tickets.show', $repairTicket)
                ->with('success', __('Repair ticket created successfully'));

        } catch (\Exception $e) {
            throw $e;
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', __('Error creating repair ticket'))
                ->withInput();
        }
    }

    public function show(RepairTicket $repairTicket)
    {
        $repairTicket->load(['customer', 'product', 'technician', 'creator', 'photos', 'statusHistories.user']);

        return view('repair-tickets.show', compact('repairTicket'));
    }

    public function edit(RepairTicket $repairTicket)
    {
        $customers = Customer::all();
        $products = Product::all();
        $technicians = User::role('technicien')->get();
        $drivers = Driver::all();

        return view('repair-tickets.edit', compact('repairTicket', 'customers', 'products', 'technicians', 'drivers'));
    }

    public function update(UpdateRequest $request, RepairTicket $repairTicket)
    {
        DB::beginTransaction();

        try {
            $repairTicket->update([
                'product_id' => $request->product_id,
                'customer_id' => $request->brought_by === 'customer' ? $request->customer_id : null,
                'driver_id' => $request->brought_by === 'driver' ? $request->driver_id : null,
                'brought_by' => $request->brought_by,
                'technician_id' => $request->technician_id,
                'serial_number' => $request->serial_number,
                'problem_description' => $request->problem_description,
                'status' => $request->status,
            ]);

            // Handle new photo uploads
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $path = $photo->store('repair-photos', 'public');
                    $repairTicket->photos()->create([
                        'photo_path' => $path,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('repair-tickets.show', $repairTicket)
                ->with('success', __('Repair ticket updated successfully'));

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', __('Error updating repair ticket'))
                ->withInput();
        }
    }

    public function updateStatus(Request $request, RepairTicket $repairTicket)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:RECEIVED,IN_PROGRESS,REPAIRED,UNREPAIRABLE,DELIVERED'],
            'status_comment' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $repairTicket->update([
                'status' => $validated['status'],
            ]);

            return redirect()
                ->back()
                ->with('success', __('Status updated successfully'));

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', __('Error updating status'));
        }
    }

    public function destroy(RepairTicket $repairTicket)
    {
        try {
            // Delete associated photos from storage
            foreach ($repairTicket->photos as $photo) {
                Storage::disk('public')->delete($photo->photo_path);
            }

            $repairTicket->delete();

            return redirect()
                ->route('repair-tickets.index')
                ->with('success', __('Repair ticket deleted successfully'));

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', __('Error deleting repair ticket'));
        }
    }
}
