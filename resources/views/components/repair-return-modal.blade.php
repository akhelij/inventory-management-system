@props(['repairTicket'])

@php
    $customers = \App\Models\Customer::all();
    $drivers = \App\Models\Driver::all();
@endphp

<div class="modal modal-blur fade" id="returnModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('repair-tickets.process-return', $repairTicket) }}" method="POST" enctype="multipart/form-data" 
                  x-data="{
                      collectedBy: '{{ $repairTicket->brought_by }}',
                      customerId: null,
                      driverId: null,
                      showOther: false
                  }">
                @csrf
                @method('PUT')
                
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Process Return') }} - #{{ $repairTicket->ticket_number }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <!-- Who is collecting -->
                    <div class="mb-3">
                        <label class="form-label required">{{ __('Collected By') }}</label>
                        <div class="form-selectgroup mb-2">
                            <label class="form-selectgroup-item">
                                <input type="radio" name="collected_by" value="customer" class="form-selectgroup-input" 
                                       x-model="collectedBy"
                                       @click="showOther = false">
                                <span class="form-selectgroup-label">{{ __('Customer') }}</span>
                            </label>
                            <label class="form-selectgroup-item">
                                <input type="radio" name="collected_by" value="driver" class="form-selectgroup-input"
                                       x-model="collectedBy"
                                       @click="showOther = false">
                                <span class="form-selectgroup-label">{{ __('Driver') }}</span>
                            </label>
                            <label class="form-selectgroup-item">
                                <input type="radio" name="collected_by" value="other" class="form-selectgroup-input"
                                       x-model="collectedBy"
                                       @click="showOther = true">
                                <span class="form-selectgroup-label">{{ __('Other') }}</span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Customer selector -->
                    <div class="mb-3" x-show="collectedBy === 'customer'" x-transition>
                        <label class="form-label required">{{ __('Select Customer') }}</label>
                        <select name="customer_id" class="form-select" required x-bind:required="collectedBy === 'customer'">
                            <option value="">{{ __('Select Customer') }}</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" @selected($repairTicket->brought_by === 'customer' && $repairTicket->customer_id === $customer->id)>
                                    {{ $customer->name }} - {{ $customer->phone }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-hint">{{ __('Select who is collecting the repaired item') }}</small>
                    </div>
                    
                    <!-- Driver selector -->
                    <div class="mb-3" x-show="collectedBy === 'driver'" x-transition>
                        <label class="form-label required">{{ __('Select Driver') }}</label>
                        <select name="driver_id" class="form-select" required x-bind:required="collectedBy === 'driver'">
                            <option value="">{{ __('Select Driver') }}</option>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}" @selected($repairTicket->brought_by === 'driver' && $repairTicket->driver_id === $driver->id)>
                                    {{ $driver->name }} - {{ $driver->phone }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-hint">{{ __('Select the driver collecting the repaired item') }}</small>
                    </div>
                    
                    <!-- Other collector info -->
                    <div class="mb-3" x-show="showOther" x-transition>
                        <label class="form-label required">{{ __('Collector Name') }}</label>
                        <input type="text" name="collector_name" class="form-control" 
                               placeholder="{{ __('Enter the name of the person collecting this item') }}"
                               x-bind:required="collectedBy === 'other'">
                        <small class="form-hint">{{ __('Name of the person collecting the item') }}</small>
                    </div>
                    
                    <!-- Return photos -->
                    <div class="mb-3">
                        <label class="form-label">{{ __('Return Condition Photos') }}</label>
                        <input type="file" name="return_photos[]" class="form-control" multiple>
                        <small class="form-hint">{{ __('Optional - document the condition upon return') }}</small>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Complete Return Process') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endpush 