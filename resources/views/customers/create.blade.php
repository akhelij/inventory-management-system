@extends('layouts.tabler')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center mb-3">
                <div class="col">
                    <h2 class="page-title">
                        {{ ($category ?? 'b2b') === 'b2c' ? __('Add Client') : __('Create Customer') }}
                    </h2>
                </div>
            </div>

            @include('partials._breadcrumbs')
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            @if (($category ?? 'b2b') === 'b2c')
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-id-card me-2"></i>{{ __('Scan CIN (optional)') }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <livewire:cin-scanner />
                    </div>
                </div>
            @endif

            <div class="row row-cards">
                <form action="{{ route('customers.store') }}" method="POST" enctype="multipart/form-data"
                      @if (($category ?? 'b2b') === 'b2c')
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
                    <input type="hidden" name="category" value="{{ $category ?? 'b2b' }}">
                    @if (($category ?? 'b2b') === 'b2c')
                        <input type="hidden" name="cin_photo" x-ref="cin_photo" value="">
                    @endif
                    <div class="row">
                        @if (($category ?? 'b2b') !== 'b2c')
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h3 class="card-title">
                                            {{ __('Customer Image') }}
                                        </h3>

                                        <img class="img-account-profile rounded-circle mb-2"
                                             src="{{ asset('assets/img/demo/user-placeholder.svg') }}" alt=""
                                             id="image-preview"/>

                                        <div class="small font-italic text-muted mb-2">{{ __('JPG or PNG no larger than 2 MB') }}</div>

                                        <input class="form-control @error('photo') is-invalid @enderror" type="file"
                                               id="image" name="photo" accept="image/*" onchange="previewImage();">

                                        @error('photo')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="{{ ($category ?? 'b2b') === 'b2c' ? 'col-lg-12' : 'col-lg-8' }}">
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title">
                                        {{ ($category ?? 'b2b') === 'b2c' ? __('Client Details') : __('Customer Details') }}
                                    </h3>

                                    <div class="row row-cards">
                                        <div class="col-sm-6 col-md-6">
                                            <div class="mb-3">
                                                <label for="name" class="form-label required">{{ __('Name') }}</label>
                                                <input type="text" id="name" name="name"
                                                       x-ref="name"
                                                       value="{{ old('name') }}"
                                                       class="form-control @error('name') is-invalid @enderror" required>
                                                @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-sm-6 col-md-6">
                                            <x-input name="email" label="{{ __('Email address') }}"/>
                                        </div>

                                        <div class="col-sm-6 col-md-6">
                                            <x-input name="phone" label="{{ __('Phone Number') }}"/>
                                        </div>

                                        <div class="col-sm-6 col-md-6">
                                            <x-input name="city" label="{{ __('City') }}"/>
                                        </div>

                                        @if (($category ?? 'b2b') === 'b2c')
                                            <div class="col-sm-6 col-md-6">
                                                <div class="mb-3">
                                                    <label for="cin" class="form-label">{{ __('CIN') }}</label>
                                                    <input type="text" id="cin" name="cin"
                                                           x-ref="cin"
                                                           value="{{ old('cin') }}"
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
                                                           value="{{ old('date_of_birth') }}"
                                                           class="form-control @error('date_of_birth') is-invalid @enderror">
                                                    @error('date_of_birth')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        @endif

                                        @if (($category ?? 'b2b') !== 'b2c')
                                            <div class="col-sm-6 col-md-6">
                                                <x-input name="limit" label="{{ __('Limit') }}"/>
                                            </div>
                                        @endif

                                        <div class="col-sm-6 col-md-6">
                                            <div class="mb-3">
                                                <label for="bank_name" class="form-label">
                                                    {{ __('Bank Name') }}
                                                </label>

                                                <select
                                                    class="form-select form-control-solid @error('bank_name') is-invalid @enderror"
                                                    id="bank_name" name="bank_name">
                                                    <option selected="" disabled="">{{ __('Select a bank:') }}</option>
                                                    @foreach ($banks as $bank)
                                                        <option value="{{ $bank->value }}" @selected(old('bank_name') === $bank->value)>
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
                                        </div>

                                        <div class="col-sm-6 col-md-6">
                                            <x-input label="{{ __('Account holder') }}" name="account_holder"/>
                                        </div>

                                        <div class="col-sm-6 col-md-6">
                                            <x-input label="{{ __('Account number') }}" name="account_number"/>
                                        </div>

                                        <div class="col-12">
                                            <div class="mb-3">
                                                <label for="address" class="form-label">
                                                    {{ __('Address') }}
                                                </label>

                                                <textarea name="address"
                                                          id="address"
                                                          x-ref="address"
                                                          rows="3"
                                                          class="form-control form-control-solid @error('address') is-invalid @enderror"
                                                >{{ old('address') }}</textarea>

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
                                        {{ __('Save') }}
                                    </button>

                                    <a class="btn btn-outline-warning" href="{{ route('customers.index', ['category' => $category ?? 'b2b']) }}">
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

@pushonce('page-scripts')
    <script src="{{ asset('assets/js/img-preview.js') }}"></script>
@endpushonce
