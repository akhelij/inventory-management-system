@props(['repairTicket'])

@php
    $technicians = \App\Models\User::role('technicien')->get();
    $customers = \App\Models\Customer::all();
    $drivers = \App\Models\Driver::all();
@endphp

<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ __('Next Actions') }}</h3>
    </div>
    <div class="card-body">
        <div class="d-flex flex-column gap-2">
            @if($repairTicket->status === 'RECEIVED')
                <form action="{{ route('repair-tickets.update-status', $repairTicket) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="IN_PROGRESS">
                    
                    <div class="mb-3">
                        <label class="form-label required">{{ __('Assign Technician') }}</label>
                        <select name="technician_id" class="form-select @error('technician_id') is-invalid @enderror" required>
                            <option value="">{{ __('Select Technician') }}</option>
                            @foreach($technicians as $technician)
                                <option value="{{ $technician->id }}" @selected(old('technician_id') == $technician->id)>
                                    {{ $technician->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('technician_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-hint">{{ __('A technician must be assigned before starting repair') }}</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-tools" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M3 21h4l13 -13a1.5 1.5 0 0 0 -4 -4l-13 13v4" />
                            <path d="M14.5 5.5l4 4" />
                            <path d="M12 8l-5 -5l-4 4l5 5" />
                            <path d="M7 8l-1.5 1.5" />
                            <path d="M16 12l5 5l-4 4l-5 -5" />
                            <path d="M16 17l-1.5 1.5" />
                        </svg>
                        {{ __('Start Repair') }}
                    </button>
                </form>
            @endif

            @if($repairTicket->status === 'IN_PROGRESS')
                <form action="{{ route('repair-tickets.update-status', $repairTicket) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label required">{{ __('Result') }}</label>
                        <div class="form-selectgroup">
                            <label class="form-selectgroup-item">
                                <input type="radio" name="status" value="REPAIRED" class="form-selectgroup-input" checked>
                                <span class="form-selectgroup-label">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-check text-success" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M5 12l5 5l10 -10" />
                                    </svg>
                                    {{ __('Repaired') }}
                                </span>
                            </label>
                            <label class="form-selectgroup-item">
                                <input type="radio" name="status" value="UNREPAIRABLE" class="form-selectgroup-input">
                                <span class="form-selectgroup-label">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-x text-danger" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M18 6l-12 12" />
                                        <path d="M6 6l12 12" />
                                    </svg>
                                    {{ __('Unrepairable') }}
                                </span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label required">{{ __('Details') }}</label>
                        <textarea name="details" class="form-control" rows="3" placeholder="{{ __('Describe what was repaired or why it cannot be repaired...') }}" required></textarea>
                        <small class="form-hint" id="details-hint">{{ __('Include parts replaced if applicable') }}</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        {{ __('Complete Repair Process') }}
                    </button>
                </form>
                
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const statusRadios = document.querySelectorAll('input[name="status"]');
                        const detailsField = document.querySelector('textarea[name="details"]');
                        const detailsHint = document.querySelector('#details-hint');
                        
                        function updateFieldsBasedOnStatus() {
                            const selectedStatus = document.querySelector('input[name="status"]:checked').value;
                            
                            if (selectedStatus === 'REPAIRED') {
                                detailsField.placeholder = "{{ __('Describe the repairs performed and any parts replaced...') }}";
                                detailsHint.textContent = "{{ __('Include parts replaced if applicable') }}";
                            } else {
                                detailsField.placeholder = "{{ __('Please explain why the item cannot be repaired...') }}";
                                detailsHint.textContent = "{{ __('Provide details about the issue that prevents repair') }}";
                            }
                        }
                        
                        statusRadios.forEach(radio => {
                            radio.addEventListener('change', updateFieldsBasedOnStatus);
                        });
                        
                        // Initial setup
                        updateFieldsBasedOnStatus();
                    });
                </script>
            @endif

            @if($repairTicket->status === 'REPAIRED' || $repairTicket->status === 'UNREPAIRABLE')
                <form action="{{ route('repair-tickets.process-return', $repairTicket) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label required">{{ __('Collected By') }}</label>
                        <div class="form-selectgroup mb-2">
                            <label class="form-selectgroup-item">
                                <input type="radio" name="collected_by" value="customer" class="form-selectgroup-input" 
                                       x-model="collectedBy"
                                       @click="showOther = false" checked>
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
                    
                    <div class="mb-3 customer-selector">
                        <label class="form-label required">{{ __('Select Customer') }}</label>
                        <select name="customer_id" class="form-select" required>
                            <option value="">{{ __('Select Customer') }}</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" @selected($repairTicket->brought_by === 'customer' && $repairTicket->customer_id === $customer->id)>
                                    {{ $customer->name }} - {{ $customer->phone }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3 driver-selector" style="display: none;">
                        <label class="form-label required">{{ __('Select Driver') }}</label>
                        <select name="driver_id" class="form-select">
                            <option value="">{{ __('Select Driver') }}</option>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}" @selected($repairTicket->brought_by === 'driver' && $repairTicket->driver_id === $driver->id)>
                                    {{ $driver->name }} - {{ $driver->phone }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3 other-collector-info" style="display: none;">
                        <label class="form-label required">{{ __('Collector Name') }}</label>
                        <input type="text" name="collector_name" class="form-control" 
                               placeholder="{{ __('Enter the name of the person collecting this item') }}">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrow-forward" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M15 11l4 4l-4 4m4 -4h-11a4 4 0 0 1 0 -8h1" />
                        </svg>
                        {{ __('Complete Return Process') }}
                    </button>
                    
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const collectedByInputs = document.querySelectorAll('input[name="collected_by"]');
                            const customerSelector = document.querySelector('.customer-selector');
                            const driverSelector = document.querySelector('.driver-selector');
                            const otherCollectorInfo = document.querySelector('.other-collector-info');
                            
                            function toggleSelectors() {
                                const selectedValue = document.querySelector('input[name="collected_by"]:checked').value;
                                
                                customerSelector.style.display = selectedValue === 'customer' ? 'block' : 'none';
                                customerSelector.querySelector('select').required = selectedValue === 'customer';
                                
                                driverSelector.style.display = selectedValue === 'driver' ? 'block' : 'none';
                                driverSelector.querySelector('select').required = selectedValue === 'driver';
                                
                                otherCollectorInfo.style.display = selectedValue === 'other' ? 'block' : 'none';
                                otherCollectorInfo.querySelector('input').required = selectedValue === 'other';
                            }
                            
                            collectedByInputs.forEach(input => {
                                input.addEventListener('change', toggleSelectors);
                            });
                            
                            // Initial setup
                            toggleSelectors();
                        });
                    </script>
                </form>
            @endif
        </div>
    </div>
</div> 