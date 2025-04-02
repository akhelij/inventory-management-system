@extends('layouts.tabler')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        {{ __('New Repair Ticket') }}
                    </h2>
                    <p class="text-muted mt-1">{{ __('Record details of a new item requiring repair') }}</p>
                </div>
            </div>
            
            @include('partials._breadcrumbs')
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <x-alert/>

            <!-- Progress indicator -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="steps steps-counter steps-blue">
                        <a href="#" class="step-item active current">
                            <div class="step-item-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-clipboard-text" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" />
                                    <path d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" />
                                    <path d="M9 12h6" />
                                    <path d="M9 16h6" />
                                </svg>
                            </div>
                            <div class="step-item-label">{{ __('Record Details') }}</div>
                        </a>
                        <a href="#" class="step-item">
                            <div class="step-item-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-tools" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M3 21h4l13 -13a1.5 1.5 0 0 0 -4 -4l-13 13v4" />
                                    <path d="M14.5 5.5l4 4" />
                                    <path d="M12 8l-5 -5l-4 4l5 5" />
                                    <path d="M7 8l-1.5 1.5" />
                                    <path d="M16 12l5 5l-4 4l-5 -5" />
                                    <path d="M16 17l-1.5 1.5" />
                                </svg>
                            </div>
                            <div class="step-item-label">{{ __('Begin Repair') }}</div>
                        </a>
                        <a href="#" class="step-item">
                            <div class="step-item-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-check-circle" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
                                    <path d="M9 12l2 2l4 -4" />
                                </svg>
                            </div>
                            <div class="step-item-label">{{ __('Complete Repair') }}</div>
                        </a>
                        <a href="#" class="step-item">
                            <div class="step-item-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrow-back" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M9 11l-4 4l4 4m-4 -4h11a4 4 0 0 0 0 -8h-1" />
                                </svg>
                            </div>
                            <div class="step-item-label">{{ __('Return to Customer') }}</div>
                        </a>
                    </div>
                </div>
            </div>

                <form action="{{ route('repair-tickets.store') }}" method="POST" enctype="multipart/form-data"
                      x-data="{
                            brought_by: '{{ old('brought_by', 'customer') }}'
                        }">
                    @csrf

                <div class="row row-cards">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <ul class="nav nav-tabs card-header-tabs" data-bs-toggle="tabs">
                                    <li class="nav-item">
                                        <a href="#tabs-item" class="nav-link active" data-bs-toggle="tab">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                <path d="M12 12m-6 0a6 6 0 1 0 12 0a6 6 0 1 0 -12 0" />
                                                <path d="M12 12m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                                <path d="M12 18.5l-3 -1.5" />
                                                <path d="M12 18.5l3 -1.5" />
                                                <path d="M12 18.5v-2.5" />
                                            </svg>
                                            {{ __('Item Details') }}
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#tabs-customer" class="nav-link" data-bs-toggle="tab">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
                                                <path d="M12 10m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                                                <path d="M6.168 18.849a4 4 0 0 1 3.832 -2.849h4a4 4 0 0 1 3.834 2.855" />
                                            </svg>
                                            {{ __('Customer/Driver') }}
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#tabs-problem" class="nav-link" data-bs-toggle="tab">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
                                                <path d="M12 9v4" />
                                                <path d="M12 16v.01" />
                                            </svg>
                                            {{ __('Problem Description') }}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-body">
                                <div class="tab-content">
                                    <!-- Item Details Tab -->
                                    <div class="tab-pane active show" id="tabs-item">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('Ticket Number') }}</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-azure-lt">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-ticket" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                                <path d="M15 5l0 2" />
                                                                <path d="M15 11l0 2" />
                                                                <path d="M15 17l0 2" />
                                                                <path d="M5 5h14a2 2 0 0 1 2 2v3a2 2 0 0 0 0 4v3a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-3a2 2 0 0 0 0 -4v-3a2 2 0 0 1 2 -2" />
                                                            </svg>
                                                        </span>
                                                        <input type="text"
                                                            name="ticket_number"
                                                            class="form-control @error('ticket_number') is-invalid @enderror"
                                                            value="{{ old('ticket_number') }}"
                                                            placeholder="{{ __('Auto-generated if left empty') }}">
                                                    </div>
                                                    @error('ticket_number')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                    <small class="form-hint">{{ __('Optional: Leave empty for auto-generated number') }}</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label required">{{ __('Product') }}</label>
                                                    <select name="product_id" class="form-select @error('product_id') is-invalid @enderror">
                                                        <option value="">{{ __('Select Product') }}</option>
                                                        @foreach($products as $product)
                                                            <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>
                                                                {{ $product->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('product_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('Serial Number') }}</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-azure-lt">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-barcode" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                                <path d="M4 7v-1a2 2 0 0 1 2 -2h2" />
                                                                <path d="M4 17v1a2 2 0 0 0 2 2h2" />
                                                                <path d="M16 4h2a2 2 0 0 1 2 2v1" />
                                                                <path d="M16 20h2a2 2 0 0 0 2 -2v-1" />
                                                                <path d="M5 11h1v2h-1z" />
                                                                <path d="M10 11l0 2" />
                                                                <path d="M14 11h1v2h-1z" />
                                                                <path d="M19 11l0 2" />
                                                            </svg>
                                                        </span>
                                                        <input type="text"
                                                            name="serial_number"
                                                            class="form-control @error('serial_number') is-invalid @enderror"
                                                            value="{{ old('serial_number') }}"
                                                            placeholder="{{ __('Enter device serial number') }}">
                                                    </div>
                                                    @error('serial_number')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Customer/Driver Tab -->
                                    <div class="tab-pane" id="tabs-customer">
                                        <div class="mb-3">
                                            <label class="form-label required">{{ __('Brought By') }}</label>
                                            <div class="form-selectgroup form-selectgroup-boxes d-flex flex-column">
                                                <label class="form-selectgroup-item flex-fill">
                                                    <input type="radio" name="brought_by" value="customer" class="form-selectgroup-input" 
                                                        x-on:change="brought_by = 'customer'"
                                                        {{ old('brought_by', 'customer') == 'customer' ? 'checked' : '' }}>
                                                    <div class="form-selectgroup-label d-flex align-items-center p-3">
                                                        <div class="me-3">
                                                            <span class="form-selectgroup-check"></span>
                                                        </div>
                                                        <div>
                                                            <span class="payment-provider-icon payment-provider-mastercard me-1" aria-hidden="true">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-user" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                                    <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
                                                                    <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                                                                </svg>
                                                            </span>
                                                            <strong>{{ __('Customer') }}</strong>
                                                            <span class="d-block text-muted">{{ __('End user of the device') }}</span>
                                                        </div>
                                                    </div>
                                                </label>
                                                <label class="form-selectgroup-item flex-fill">
                                                    <input type="radio" name="brought_by" value="driver" class="form-selectgroup-input"
                                                        x-on:change="brought_by = 'driver'"
                                                        {{ old('brought_by') == 'driver' ? 'checked' : '' }}>
                                                    <div class="form-selectgroup-label d-flex align-items-center p-3">
                                                        <div class="me-3">
                                                            <span class="form-selectgroup-check"></span>
                                                        </div>
                                                        <div>
                                                            <span class="payment-provider-icon payment-provider-visa me-1" aria-hidden="true">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-car" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                                    <path d="M7 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                                                    <path d="M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                                                    <path d="M5 17h-2v-6l2 -5h9l4 5h1a2 2 0 0 1 2 2v4h-2m-4 0h-6m-6 -6h15m-6 0v-5" />
                                                                </svg>
                                                            </span>
                                                            <strong>{{ __('Driver') }}</strong>
                                                            <span class="d-block text-muted">{{ __('Delivery personnel') }}</span>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>

                    <div class="row">
                                            <div class="col-md-12" x-show="brought_by === 'customer'">
                                                <div class="mb-3">
                                                    <label class="form-label required">{{ __('Customer') }}</label>
                                                    <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror">
                                                        <option value="">{{ __('Select Customer') }}</option>
                                                        @foreach($customers as $customer)
                                                            <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>
                                                                {{ $customer->name }} - {{ $customer->phone }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('customer_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-12" x-show="brought_by === 'driver'">
                                                <div class="mb-3">
                                                    <label class="form-label required">{{ __('Driver') }}</label>
                                                    <select name="driver_id" class="form-select @error('driver_id') is-invalid @enderror">
                                                        <option value="">{{ __('Select Driver') }}</option>
                                                        @foreach($drivers as $driver)
                                                            <option value="{{ $driver->id }}" @selected(old('driver_id') == $driver->id)>
                                                                {{ $driver->name }} - {{ $driver->phone }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('driver_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Problem Description Tab -->
                                    <div class="tab-pane" id="tabs-problem">
                                        <div class="mb-3">
                                            <label class="form-label required">{{ __('Problem Description') }}</label>
                                            <textarea name="problem_description"
                                                    rows="6"
                                                    class="form-control @error('problem_description') is-invalid @enderror"
                                                    placeholder="{{ __('Describe the issue in detail...') }}">{{ old('problem_description') }}</textarea>
                                            @error('problem_description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-hint">{{ __('Include any relevant details about when the problem started and what exactly is happening') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer d-flex align-items-center">
                                <button type="submit" class="btn btn-primary ms-auto">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-device-floppy" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                                        <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                        <path d="M14 4l0 4l-6 0l0 -4" />
                                    </svg>
                                    {{ __('Create Repair Ticket') }}
                                </button>
                            </div>
                        </div>
                    </div>
                    
                        <div class="col-lg-4">
                            <div class="card">
                            <div class="card-header">
                                    <h3 class="card-title">
                                    {{ __('Photos of Item Condition') }}
                                    </h3>
                            </div>
                            <div class="card-body">
                                    <div
                                        x-data="{
                                            previewUrls: [],
                                            handleFiles(event) {
                                                const files = Array.from(event.target.files);

                                                if (files.length > 3) {
                                                    alert('You can only upload up to 3 photos');
                                                    event.target.value = '';
                                                    return;
                                                }

                                                this.previewUrls = [];
                                                files.forEach(file => {
                                                    const reader = new FileReader();
                                                    reader.onload = (e) => {
                                                        this.previewUrls.push(e.target.result);
                                                    };
                                                    reader.readAsDataURL(file);
                                                });
                                            }
                                        }"
                                        class="mb-3"
                                    >
                                    <div class="form-label">{{ __('Upload Photos (Optional)') }}</div>
                                        <input
                                            type="file"
                                            class="form-control @error('photos.*') is-invalid @enderror"
                                            name="photos[]"
                                            accept="image/*"
                                            multiple
                                            x-on:change="handleFiles"
                                        >
                                        @error('photos.*')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    <small class="form-hint">{{ __('Document the current condition of the item (max 3 photos)') }}</small>

                                        <!-- Preview Container -->
                                        <div class="row g-2 mt-2">
                                            <template x-for="(url, index) in previewUrls" :key="index">
                                                <div class="col-6">
                                                    <div class="position-relative">
                                                        <img
                                                            :src="url"
                                                            class="img-fluid rounded"
                                                            style="aspect-ratio: 1; object-fit: cover; width: 100%;"
                                                        >
                                                        <button
                                                            type="button"
                                                            class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1"
                                                            x-on:click="previewUrls.splice(index, 1)"
                                                        >
                                                            ×
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-3">
                                <div class="card-header">
                                <h3 class="card-title">{{ __('What Happens Next?') }}</h3>
                                </div>
                                <div class="card-body">
                                <ol class="ps-3">
                                    <li class="mb-2">{{ __('After creating the ticket, the item will be registered in the system with "RECEIVED" status.') }}</li>
                                    <li class="mb-2">{{ __('You will need to assign a technician and then start the repair process by clicking "Start Repair" on the ticket page.') }}</li>
                                    <li class="mb-2">{{ __('When repairs are complete, update the ticket status to "REPAIRED" or "UNREPAIRABLE".') }}</li>
                                    <li>{{ __('Finally, process the return to document when the customer receives their item back.') }}</li>
                                </ol>
                                <div class="alert alert-info mt-3" role="alert">
                                    <div class="d-flex">
                                        <div>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
                                                <path d="M12 8l.01 0" />
                                                <path d="M11 12l1 0l0 4l1 0" />
                                            </svg>
                                        </div>
                                        <div>
                                            {{ __('Required fields are marked with a red asterisk.') }}
                                        </div>
                                    </div>
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const tabLinks = document.querySelectorAll('.nav-link');
            const tabContents = document.querySelectorAll('.tab-pane');
            
            // Form submission validation
            form.addEventListener('submit', function(e) {
                const requiredFields = form.querySelectorAll('[required], .form-label.required + div select, .form-label.required + div input, .form-label.required + div textarea');
                let hasErrors = false;
                let firstErrorTab = null;
                
                requiredFields.forEach(field => {
                    if (!field.value) {
                        field.classList.add('is-invalid');
                        
                        if (!hasErrors) {
                            // Find which tab contains this field
                            const tabPane = field.closest('.tab-pane');
                            firstErrorTab = tabPane ? tabPane.id : null;
                        }
                        
                        hasErrors = true;
                    } else {
                        field.classList.remove('is-invalid');
                    }
                });
                
                // Customer/driver validation - ensure one is selected based on brought_by value
                const broughtBy = document.querySelector('input[name="brought_by"]:checked').value;
                if (broughtBy === 'customer') {
                    const customerId = document.querySelector('select[name="customer_id"]');
                    if (!customerId.value) {
                        customerId.classList.add('is-invalid');
                        if (!hasErrors) {
                            firstErrorTab = 'tabs-customer';
                        }
                        hasErrors = true;
                    }
                } else if (broughtBy === 'driver') {
                    const driverId = document.querySelector('select[name="driver_id"]');
                    if (!driverId.value) {
                        driverId.classList.add('is-invalid');
                        if (!hasErrors) {
                            firstErrorTab = 'tabs-customer';
                        }
                        hasErrors = true;
                    }
                }
                
                if (hasErrors) {
                    e.preventDefault();
                    
                    // Switch to the tab with the first error
                    if (firstErrorTab) {
                        tabLinks.forEach(link => {
                            link.classList.remove('active');
                            if (link.getAttribute('href') === '#' + firstErrorTab) {
                                link.classList.add('active');
                            }
                        });
                        
                        tabContents.forEach(content => {
                            content.classList.remove('active', 'show');
                            if (content.id === firstErrorTab) {
                                content.classList.add('active', 'show');
                            }
                        });
                    }
                    
                    // Show alert about required fields
                    const alertHtml = `
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            <div class="d-flex">
                                <div>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"></path>
                                        <path d="M12 8l0 4"></path>
                                        <path d="M12 16l.01 0"></path>
                                    </svg>
                                </div>
                                <div>
                                    {{ __('Please fill in all required fields.') }}
                                </div>
                            </div>
                            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
    </div>
                    `;
                    
                    const alertContainer = document.querySelector('.container-xl > x-alert');
                    alertContainer.insertAdjacentHTML('afterend', alertHtml);
                    
                    // Scroll to the top to show the error
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            });
            
            // Add required attribute to fields with required label
            document.querySelectorAll('.form-label.required + div select, .form-label.required + div input, .form-label.required + div textarea').forEach(field => {
                field.setAttribute('required', true);
            });
        });
    </script>
    @endpush
@endsection
