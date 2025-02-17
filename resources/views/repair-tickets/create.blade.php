@extends('layouts.tabler')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <x-alert/>

            <div class="row row-cards">
                <form action="{{ route('repair-tickets.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title">
                                        {{ __('Product Photos') }}
                                    </h3>

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
                                        <div class="form-label">{{ __('Upload Photos (Max 3)') }}</div>
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
                        </div>

                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">{{ __('Repair Ticket Details') }}</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row row-cards">
                                        <div class="col-sm-12">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('Numéro de bon de commande') }}</label>
                                                <input type="text"
                                                       name="ticket_number"
                                                       class="form-control @error('ticket_number') is-invalid @enderror"
                                                       value="{{ old('ticket_number') }}"
                                                       placeholder="{{ __('Entrer numéro de bon de commande') }}">
                                                @error('ticket_number')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-sm-6 col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label required">{{ __('Customer') }}</label>
                                                <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror">
                                                    <option value="">{{ __('Select Customer') }}</option>
                                                    @foreach($customers as $customer)
                                                        <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>
                                                            {{ $customer->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('customer_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-sm-6 col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('Technician') }}</label>
                                                <select name="technician_id" class="form-select @error('technician_id') is-invalid @enderror">
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
                                            </div>
                                        </div>

                                        <div class="col-sm-6 col-md-6">
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

                                        <div class="col-sm-6 col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('Numéro de série') }}</label>
                                                <input type="text"
                                                       name="serial_number"
                                                       class="form-control @error('serial_number') is-invalid @enderror"
                                                       value="{{ old('serial_number') }}"
                                                       placeholder="{{ __('Enter serial Number') }}">
                                                @error('serial_number')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label required">{{ __('Problem Description') }}</label>
                                                <textarea name="problem_description"
                                                          rows="4"
                                                          class="form-control @error('problem_description') is-invalid @enderror"
                                                          placeholder="{{ __('Describe the problem...') }}">{{ old('problem_description') }}</textarea>
                                                @error('problem_description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-end">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('Create Ticket') }}
                                    </button>
                                    <a href="{{ route('repair-tickets.index') }}" class="btn btn-default">
                                        {{ __('Cancel') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
