@extends('layouts.tabler')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center mb-3">
                <div class="col">
                    <h2 class="page-title">
                        {{ $customer->category?->value === 'b2c' ? __('Edit Client') : __('Edit Customer') }}
                    </h2>
                </div>
            </div>

            @include('partials._breadcrumbs', ['model' => $customer])
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">

                <form action="{{ route('customers.update', $customer->uuid) }}" method="POST"
                      enctype="multipart/form-data"
                      @if ($customer->category?->value === 'b2c')
                          x-data="{}"
                          x-on:cin-scanned.window="
                              const d = $event.detail.data || $event.detail;
                              if (d.name) $refs.name.value = d.name;
                              if (d.cin) $refs.cin.value = d.cin;
                              if (d.date_of_birth) $refs.date_of_birth.value = d.date_of_birth;
                              if (d.address) $refs.address.value = d.address;
                              if (d.cin_photo) $refs.cin_photo.value = d.cin_photo;
                          "
                      @endif
                >
                    @csrf
                    @method('put')
                    @if ($customer->category?->value === 'b2c')
                        <input type="hidden" name="cin_photo" x-ref="cin_photo" value="{{ old('cin_photo', $customer->cin_photo) }}">
                    @endif
                    <div class="row">
                        @if ($customer->category?->value === 'b2c')
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-id-card me-2"></i>{{ __('CIN Scanner') }}
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <livewire:cin-scanner />

                                    @if ($customer->cin_photo)
                                        <div class="mt-3">
                                            <label class="form-label small text-muted">{{ __('Current CIN Photo') }}</label>
                                            <img src="{{ asset('storage/' . $customer->cin_photo) }}" alt="CIN" class="img-fluid rounded" style="max-height: 160px;">
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="{{ $customer->category?->value === 'b2c' ? 'col-lg-8' : 'col-lg-12' }}">
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title">
                                        {{ $customer->category?->value === 'b2c' ? __('Client Details') : __('Customer Details') }}
                                    </h3>

                                    <div class="row row-cards">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="name" class="form-label required">{{ __('Name') }}</label>
                                                <input type="text" id="name" name="name"
                                                       x-ref="name"
                                                       value="{{ old('name', $customer->name) }}"
                                                       class="form-control @error('name') is-invalid @enderror" required>
                                                @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <x-input label="{{ __('Email address') }}" name="email"
                                                     :value="old('email', $customer->email)"
                                            />
                                        </div>

                                        <div class="col-sm-6 col-md-6">
                                            <x-input label="{{ __('Phone number') }}" name="phone"
                                                     :value="old('phone', $customer->phone)"
                                            />
                                        </div>

                                        <div class="col-sm-6 col-md-6">
                                            <x-input label="{{ __('City') }}" name="city"
                                                     :value="old('city', $customer->city)"
                                            />
                                        </div>

                                        @if ($customer->category?->value === 'b2c')
                                            <div class="col-sm-6 col-md-6">
                                                <div class="mb-3">
                                                    <label for="cin" class="form-label">{{ __('CIN') }}</label>
                                                    <input type="text" id="cin" name="cin"
                                                           x-ref="cin"
                                                           value="{{ old('cin', $customer->cin) }}"
                                                           class="form-control @error('cin') is-invalid @enderror">
                                                    @error('cin')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-sm-6 col-md-6">
                                                <div class="mb-3">
                                                    <label for="date_of_birth" class="form-label">{{ __('Date of Birth') }}</label>
                                                    <input type="date" id="date_of_birth" name="date_of_birth"
                                                           x-ref="date_of_birth"
                                                           value="{{ old('date_of_birth', $customer->date_of_birth?->format('Y-m-d')) }}"
                                                           class="form-control @error('date_of_birth') is-invalid @enderror">
                                                    @error('date_of_birth')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        @endif

                                        @if ($customer->category?->value !== 'b2c')
                                            <div class="col-12">
                                                <x-input label="{{ __('Limit') }}" name="limit"
                                                         :value="old('limit', $customer->limit)"
                                                />
                                            </div>
                                        @endif

                                        <div class="col-sm-6 col-md-6">
                                            <label for="bank_name" class="form-label">
                                                {{ __('Bank Name') }}
                                            </label>

                                            <select class="form-select @error('bank_name') is-invalid @enderror"
                                                    id="bank_name" name="bank_name">
                                                <option selected="" disabled="">{{ __('Select a bank:') }}</option>
                                                @foreach ($banks as $bank)
                                                    <option value="{{ $bank->value }}" @selected(old('bank_name', $customer->bank_name) === $bank->value)>
                                                        {{ $bank->label() }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            @error('bank_name')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>

                                        <div class="col-sm-6 col-md-6">
                                            <x-input label="{{ __('Account holder') }}" name="account_holder"
                                                     :value="old('account_holder', $customer->account_holder)"
                                            />
                                        </div>

                                        <div class="col-12">
                                            <x-input label="{{ __('Account number') }}" name="account_number"
                                                     :value="old('account_number', $customer->account_number)"
                                            />
                                        </div>

                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="address" class="form-label">
                                                    {{ __('Address') }}
                                                </label>

                                                <textarea id="address" name="address" rows="3"
                                                          x-ref="address"
                                                          class="form-control @error('address') is-invalid @enderror">{{ old('address', $customer->address) }}</textarea>

                                                @error('address')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-end">
                                    <button class="btn btn-primary" type="submit">
                                        {{ __('Update') }}
                                    </button>

                                    <a class="btn btn-outline-warning" href="{{ route('customers.index', ['category' => $customer->category?->value ?? 'b2b']) }}">
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
