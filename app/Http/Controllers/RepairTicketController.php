<?php

namespace App\Http\Controllers;

use App\Http\Requests\RepairTicket\StoreRequest;
use App\Http\Requests\RepairTicket\UpdateRequest;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Product;
use App\Models\RepairTicket;
use App\Models\User;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class RepairTicketController extends Controller
{
    use FileUploadTrait;

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
        $drivers = Driver::all();

        return view('repair-tickets.create', compact('customers', 'drivers', 'products'));
    }

    public function store(StoreRequest $request)
    {
        DB::beginTransaction();
        try {
            // Generate ticket number using a more reliable approach
            $randomNumber = substr(uniqid(), -3);
            $ticketNumber = $request->ticket_number ?? 'RT-'.date('Ymd').'-'.$randomNumber;

            // Create repair ticket
            $repairTicket = RepairTicket::create([
                'ticket_number' => $ticketNumber,
                'customer_id' => $request->brought_by === 'customer' ? $request->customer_id : null,
                'driver_id' => $request->brought_by === 'driver' ? $request->driver_id : null,
                'brought_by' => $request->brought_by,
                'product_id' => $request->product_id,
                'created_by' => Auth::id(),
                'serial_number' => $request->serial_number,
                'problem_description' => $request->problem_description,
                'status' => 'RECEIVED',
            ]);

            // Handle photo uploads
            if ($request->hasFile('photos')) {
                $paths = $this->uploadMultipleFiles($request->file('photos'), 'repair-photos');
                
                foreach ($paths as $path) {
                    $repairTicket->photos()->create([
                        'photo_path' => $path,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('repair-tickets.show', $repairTicket)
                ->with('success', __('Repair ticket created successfully. You can now assign a technician and begin the repair process.'));

        } catch (\Exception $e) {
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

            // Handle deletion of existing photos
            if ($request->has('photos_to_delete') && is_array($request->photos_to_delete)) {
                foreach ($request->photos_to_delete as $photoId) {
                    $photo = $repairTicket->photos()->find($photoId);
                    if ($photo) {
                        // Delete the file from storage
                        $this->deleteFile($photo->photo_path);
                        // Delete the record
                        $photo->delete();
                    }
                }
            }

            // Handle new photo uploads
            if ($request->hasFile('photos')) {
                $paths = $this->uploadMultipleFiles($request->file('photos'), 'repair-photos');
                
                foreach ($paths as $path) {
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
        // Different validation rules based on target status
        if ($request->status === 'IN_PROGRESS') {
            $validated = $request->validate([
                'status' => ['required', 'in:IN_PROGRESS'],
                'technician_id' => ['required', 'exists:users,id'],
            ]);
        } else if ($request->status === 'REPAIRED' || $request->status === 'UNREPAIRABLE') {
            $validated = $request->validate([
                'status' => ['required', 'in:REPAIRED,UNREPAIRABLE'],
                'details' => ['required', 'string'],
            ]);
        } else {
            $validated = $request->validate([
                'status' => ['required', 'in:RECEIVED,IN_PROGRESS,REPAIRED,UNREPAIRABLE,DELIVERED'],
                'status_comment' => ['nullable', 'string', 'max:255'],
            ]);
        }

        DB::beginTransaction();
        try {
            // Custom validation for status transition
            if ($request->status === 'IN_PROGRESS' && $repairTicket->status !== 'RECEIVED') {
                throw new \Exception(__('Cannot start repair for a ticket that is not in RECEIVED status'));
            }
            
            // Update data based on the target status
            $updateData = ['status' => $validated['status']];
            
            // If moving to IN_PROGRESS, assign the technician
            if ($request->status === 'IN_PROGRESS') {
                $updateData['technician_id'] = $validated['technician_id'];
            }
            
            $repairTicket->update($updateData);

            // Prepare comment for history based on status type
            $comment = $request->status_comment ?? null;
            
            if ($request->status === 'REPAIRED') {
                $comment = json_encode([
                    'resolution_details' => $validated['details'],
                    'parts_replaced' => null,
                    'comment' => null
                ]);
            } else if ($request->status === 'UNREPAIRABLE') {
                $comment = json_encode([
                    'problem_description' => $validated['details'],
                    'comment' => null
                ]);
            }
            
            // Create status history record with the appropriate comment
            $repairTicket->statusHistories()->create([
                'user_id' => Auth::id(),
                'from_status' => $repairTicket->getOriginal('status'),
                'to_status' => $validated['status'],
                'comment' => $comment,
            ]);

            DB::commit();
            return redirect()
                ->back()
                ->with('success', __('Status updated successfully'));

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', $e->getMessage() ?: __('Error updating status'));
        }
    }

    public function destroy(RepairTicket $repairTicket)
    {
        try {
            // Delete associated photos from storage
            foreach ($repairTicket->photos as $photo) {
                $this->deleteFile($photo->photo_path);
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

    /**
     * Process the return of the repaired item
     */
    public function processReturn(Request $request, RepairTicket $repairTicket)
    {
        // Validation depends on collection type
        if ($request->collected_by === 'customer') {
            $validated = $request->validate([
                'collected_by' => ['required', 'in:customer'],
                'customer_id' => ['required', 'exists:customers,id'],
                'return_photos.*' => ['nullable', 'image', 'max:2048'],
            ]);
        } else if ($request->collected_by === 'driver') {
            $validated = $request->validate([
                'collected_by' => ['required', 'in:driver'],
                'driver_id' => ['required', 'exists:drivers,id'],
                'return_photos.*' => ['nullable', 'image', 'max:2048'],
            ]);
        } else {
            $validated = $request->validate([
                'collected_by' => ['required', 'in:other'],
                'collector_name' => ['required', 'string', 'max:255'],
                'return_photos.*' => ['nullable', 'image', 'max:2048'],
            ]);
        }

        DB::beginTransaction();
        try {
            // Update the repair ticket status to DELIVERED
            $repairTicket->update([
                'status' => 'DELIVERED',
            ]);

            // Prepare the comment data based on collection type
            $commentData = ['collected_by' => $validated['collected_by']];
            
            if ($request->collected_by === 'customer') {
                $customer = \App\Models\Customer::find($validated['customer_id']);
                $commentData['customer_id'] = $validated['customer_id'];
                $commentData['collector_info'] = $customer ? $customer->name : null;
            } else if ($request->collected_by === 'driver') {
                $driver = \App\Models\Driver::find($validated['driver_id']);
                $commentData['driver_id'] = $validated['driver_id'];
                $commentData['collector_info'] = $driver ? $driver->name : null;
            } else {
                $commentData['collector_name'] = $validated['collector_name'];
            }

            // Store the return info in statusHistories
            $repairTicket->statusHistories()->create([
                'user_id' => Auth::id(),
                'from_status' => $repairTicket->getOriginal('status'),
                'to_status' => 'DELIVERED',
                'comment' => json_encode($commentData),
            ]);

            // Handle return photos if any
            if ($request->hasFile('return_photos')) {
                $paths = $this->uploadMultipleFiles($request->file('return_photos'), 'repair-return-photos');
                
                foreach ($paths as $path) {
                    $repairTicket->photos()->create([
                        'photo_path' => $path,
                        'photo_type' => 'return',
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('repair-tickets.show', $repairTicket)
                ->with('success', __('Return process completed successfully'));

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', __('Error processing return: ') . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Upload photos at any time during the repair process
     */
    public function uploadPhotos(Request $request, RepairTicket $repairTicket)
    {
        $validated = $request->validate([
            'photos.*' => ['required', 'image', 'max:5120'],
            'photo_type' => ['nullable', 'string', 'in:damage,repair,return'],
        ]);

        DB::beginTransaction();
        try {
            if ($request->hasFile('photos')) {
                $paths = $this->uploadMultipleFiles($request->file('photos'), 'repair-photos');
                
                foreach ($paths as $path) {
                    $repairTicket->photos()->create([
                        'photo_path' => $path,
                        'photo_type' => $request->photo_type ?? null,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('repair-tickets.show', $repairTicket)
                ->with('success', __('Photos uploaded successfully'));

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', __('Error uploading photos: ') . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete a specific photo from a repair ticket
     */
    public function deletePhoto($photoId)
    {
        try {
            // Find the photo
            $photo = \App\Models\RepairPhoto::findOrFail($photoId);
            
            // Get the repair ticket before we delete the photo
            $repairTicket = $photo->repairTicket;
            
            // Delete the file from storage
            $this->deleteFile($photo->photo_path);
            
            // Delete the database record
            $photo->delete();
            
            return redirect()
                ->route('repair-tickets.show', $repairTicket)
                ->with('success', __('Photo deleted successfully'));
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', __('Error deleting photo: ') . $e->getMessage());
        }
    }
}
