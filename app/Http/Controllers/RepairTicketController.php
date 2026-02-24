<?php

namespace App\Http\Controllers;

use App\Http\Requests\RepairTicket\StoreRequest;
use App\Http\Requests\RepairTicket\UpdateRequest;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Product;
use App\Models\RepairPhoto;
use App\Models\RepairTicket;
use App\Models\User;
use App\Traits\FileUploadTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RepairTicketController extends Controller
{
    use FileUploadTrait;

    public function index(): View
    {
        return view('repair-tickets.index', [
            'tickets' => RepairTicket::with(['customer', 'product', 'technician'])->latest()->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('repair-tickets.create', [
            'customers' => Customer::all(),
            'drivers' => Driver::all(),
            'products' => Product::all(),
        ]);
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $ticketNumber = $request->ticket_number ?? 'RT-'.date('Ymd').'-'.substr(uniqid(), -3);

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

            if ($request->hasFile('photos')) {
                $paths = $this->uploadMultipleFiles($request->file('photos'), 'repair-photos');
                foreach ($paths as $path) {
                    $repairTicket->photos()->create(['photo_path' => $path]);
                }
            }

            DB::commit();

            return to_route('repair-tickets.show', $repairTicket)
                ->with('success', __('Repair ticket created successfully. You can now assign a technician and begin the repair process.'));
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', __('Error creating repair ticket'))
                ->withInput();
        }
    }

    public function show(RepairTicket $repairTicket): View
    {
        $repairTicket->load(['customer', 'product', 'technician', 'creator', 'photos', 'statusHistories.user']);

        return view('repair-tickets.show', compact('repairTicket'));
    }

    public function edit(RepairTicket $repairTicket): View
    {
        return view('repair-tickets.edit', [
            'repairTicket' => $repairTicket,
            'customers' => Customer::all(),
            'products' => Product::all(),
            'technicians' => User::role('technicien')->get(),
            'drivers' => Driver::all(),
        ]);
    }

    public function update(UpdateRequest $request, RepairTicket $repairTicket): RedirectResponse
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

            if ($request->has('photos_to_delete') && is_array($request->photos_to_delete)) {
                foreach ($request->photos_to_delete as $photoId) {
                    $photo = $repairTicket->photos()->find($photoId);
                    if ($photo) {
                        $this->deleteFile($photo->photo_path);
                        $photo->delete();
                    }
                }
            }

            if ($request->hasFile('photos')) {
                $paths = $this->uploadMultipleFiles($request->file('photos'), 'repair-photos');
                foreach ($paths as $path) {
                    $repairTicket->photos()->create(['photo_path' => $path]);
                }
            }

            DB::commit();

            return to_route('repair-tickets.show', $repairTicket)
                ->with('success', __('Repair ticket updated successfully'));
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', __('Error updating repair ticket'))
                ->withInput();
        }
    }

    public function updateStatus(Request $request, RepairTicket $repairTicket): RedirectResponse
    {
        $validated = match ($request->status) {
            'IN_PROGRESS' => $request->validate([
                'status' => 'required|in:IN_PROGRESS',
                'technician_id' => 'required|exists:users,id',
            ]),
            'REPAIRED', 'UNREPAIRABLE' => $request->validate([
                'status' => 'required|in:REPAIRED,UNREPAIRABLE',
                'details' => 'required|string',
            ]),
            default => $request->validate([
                'status' => 'required|in:RECEIVED,IN_PROGRESS,REPAIRED,UNREPAIRABLE,DELIVERED',
                'status_comment' => 'nullable|string|max:255',
            ]),
        };

        DB::beginTransaction();
        try {
            if ($request->status === 'IN_PROGRESS' && $repairTicket->status !== 'RECEIVED') {
                throw new \Exception(__('Cannot start repair for a ticket that is not in RECEIVED status'));
            }

            $updateData = ['status' => $validated['status']];

            if ($request->status === 'IN_PROGRESS') {
                $updateData['technician_id'] = $validated['technician_id'];
            }

            $repairTicket->update($updateData);

            $comment = match ($request->status) {
                'REPAIRED' => json_encode([
                    'resolution_details' => $validated['details'],
                    'parts_replaced' => null,
                    'comment' => null,
                ]),
                'UNREPAIRABLE' => json_encode([
                    'problem_description' => $validated['details'],
                    'comment' => null,
                ]),
                default => $request->status_comment ?? null,
            };

            $repairTicket->statusHistories()->create([
                'user_id' => Auth::id(),
                'from_status' => $repairTicket->getOriginal('status'),
                'to_status' => $validated['status'],
                'comment' => $comment,
            ]);

            DB::commit();

            return redirect()->back()->with('success', __('Status updated successfully'));
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', $e->getMessage() ?: __('Error updating status'));
        }
    }

    public function destroy(RepairTicket $repairTicket): RedirectResponse
    {
        try {
            foreach ($repairTicket->photos as $photo) {
                $this->deleteFile($photo->photo_path);
            }

            $repairTicket->delete();

            return to_route('repair-tickets.index')->with('success', __('Repair ticket deleted successfully'));
        } catch (\Exception) {
            return redirect()->back()->with('error', __('Error deleting repair ticket'));
        }
    }

    public function processReturn(Request $request, RepairTicket $repairTicket): RedirectResponse
    {
        $validated = match ($request->collected_by) {
            'customer' => $request->validate([
                'collected_by' => 'required|in:customer',
                'customer_id' => 'required|exists:customers,id',
                'return_photos.*' => 'nullable|image|max:2048',
            ]),
            'driver' => $request->validate([
                'collected_by' => 'required|in:driver',
                'driver_id' => 'required|exists:drivers,id',
                'return_photos.*' => 'nullable|image|max:2048',
            ]),
            default => $request->validate([
                'collected_by' => 'required|in:other',
                'collector_name' => 'required|string|max:255',
                'return_photos.*' => 'nullable|image|max:2048',
            ]),
        };

        DB::beginTransaction();
        try {
            $repairTicket->update(['status' => 'DELIVERED']);

            $commentData = ['collected_by' => $validated['collected_by']];

            match ($request->collected_by) {
                'customer' => $commentData += [
                    'customer_id' => $validated['customer_id'],
                    'collector_info' => Customer::find($validated['customer_id'])?->name,
                ],
                'driver' => $commentData += [
                    'driver_id' => $validated['driver_id'],
                    'collector_info' => Driver::find($validated['driver_id'])?->name,
                ],
                default => $commentData += ['collector_name' => $validated['collector_name']],
            };

            $repairTicket->statusHistories()->create([
                'user_id' => Auth::id(),
                'from_status' => $repairTicket->getOriginal('status'),
                'to_status' => 'DELIVERED',
                'comment' => json_encode($commentData),
            ]);

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

            return to_route('repair-tickets.show', $repairTicket)
                ->with('success', __('Return process completed successfully'));
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', __('Error processing return: ').$e->getMessage())
                ->withInput();
        }
    }

    public function uploadPhotos(Request $request, RepairTicket $repairTicket): RedirectResponse
    {
        $request->validate([
            'photos.*' => 'required|image|max:5120',
            'photo_type' => 'nullable|string|in:damage,repair,return',
        ]);

        DB::beginTransaction();
        try {
            if ($request->hasFile('photos')) {
                $paths = $this->uploadMultipleFiles($request->file('photos'), 'repair-photos');
                foreach ($paths as $path) {
                    $repairTicket->photos()->create([
                        'photo_path' => $path,
                        'photo_type' => $request->photo_type,
                    ]);
                }
            }

            DB::commit();

            return to_route('repair-tickets.show', $repairTicket)
                ->with('success', __('Photos uploaded successfully'));
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', __('Error uploading photos: ').$e->getMessage())
                ->withInput();
        }
    }

    public function deletePhoto(int $photoId): RedirectResponse
    {
        try {
            $photo = RepairPhoto::findOrFail($photoId);
            $repairTicket = $photo->repairTicket;

            $this->deleteFile($photo->photo_path);
            $photo->delete();

            return to_route('repair-tickets.show', $repairTicket)
                ->with('success', __('Photo deleted successfully'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', __('Error deleting photo: ').$e->getMessage());
        }
    }
}
