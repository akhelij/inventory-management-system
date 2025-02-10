@extends('layouts.tabler')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center mb-3">
                <div class="col">
                    <h2 class="page-title">
                        {{ __('Edit Repair Ticket') }} #{{ $repairTicket->ticket_number }}
                    </h2>
                </div>
            </div>

            @include('partials._breadcrumbs')
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <form action="{{ route('repair-tickets.update', $repairTicket) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title">{{ __('Update Status') }}</h3>

                                    <div class="mb-3">
                                        <label class="form-label required">{{ __('Status') }}</label>
                                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                                            @foreach(['RECEIVED', 'IN_PROGRESS', 'REPAIRED', 'UNREPAIRABLE', 'DELIVERED'] as $status)
                                                <option value="{{ $status }}" @selected($repairTicket->status === $status)>
                                                    {{ $status }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">{{ __('Status Comment') }}</label>
                                        <textarea name="status_comment"
                                                  rows="3"
                                                  class="form-control @error('status_comment') is-invalid @enderror"
                                                  placeholder="{{ __('Add a comment about this status change...') }}">{{ old('status_comment') }}</textarea>
                                        @error('status_comment')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            @if($repairTicket->photos->count() > 0)
                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h3 class="card-title">{{ __('Current Photos') }}</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="row row-cards">
                                            @foreach($repairTicket->photos as $photo)
                                                <div class="col-6 position-relative">
                                                    <img src="{{ Storage::url($photo->photo_path) }}"
                                                         class="img-fluid rounded"
                                                         alt="Repair photo">
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="card mt-3">
                                <div class="card-body">
                                    <h3 class="card-title">{{ __('Add More Photos') }}</h3>
                                    <div class="mb-3">
                                        <div class="form-label">{{ __('Upload Photos (Max 3)') }}</div>
                                        <input type="file"
                                               class="form-control @error('photos.*') is-invalid @enderror"
                                               name="photos[]"
                                               accept="image/*"
                                               multiple>
                                        @error('photos.*')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">{{ __('Repair Details') }}</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row row-cards">
                                        <div class="col-sm-6 col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label required">{{ __('Customer') }}</label>
                                                <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror">
                                                    <option value="">{{ __('Select Customer') }}</option>
                                                    @foreach($customers as $customer)
                                                        <option value="{{ $customer->id }}"
                                                            @selected(old('customer_id', $repairTicket->customer_id) == $customer->id)>
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
                                                <label class="form-label required">{{ __('Product') }}</label>
                                                <select name="product_id" class="form-select @error('product_id') is-invalid @enderror">
                                                    <option value="">{{ __('Select Product') }}</option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->id }}"
                                                            @selected(old('product_id', $repairTicket->product_id) == $product->id)>
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
                                                <label class="form-label">{{ __('Serial Number') }}</label>
                                                <input type="text"
                                                       name="serial_number"
                                                       class="form-control @error('serial_number') is-invalid @enderror"
                                                       value="{{ old('serial_number', $repairTicket->serial_number) }}"
                                                       placeholder="{{ __('Enter Serial Number') }}">
                                                @error('serial_number')
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
                                                        <option value="{{ $technician->id }}"
                                                            @selected(old('technician_id', $repairTicket->technician_id) == $technician->id)>
                                                            {{ $technician->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('technician_id')
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
                                                          placeholder="{{ __('Describe the problem...') }}">{{ old('problem_description', $repairTicket->problem_description) }}</textarea>
                                                @error('problem_description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-end">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('Update Ticket') }}
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
