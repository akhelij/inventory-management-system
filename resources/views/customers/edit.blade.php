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
                      enctype="multipart/form-data">
                    @csrf
                    @method('put')
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title">
                                        {{ __('Profile Image') }}
                                    </h3>

                                    <img class="img-account-profile mb-2"
                                         src="{{ $customer->photo ? asset('storage/' . $customer->photo) : asset('assets/img/demo/user-placeholder.svg') }}"
                                         alt="" id="image-preview"/>

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

                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title">
                                        {{ $customer->category?->value === 'b2c' ? __('Client Details') : __('Customer Details') }}
                                    </h3>

                                    <div class="row row-cards">
                                        <div class="col-md-12">
                                            <x-input name="name" :value="old('name', $customer->name)"
                                                     :required="true"/>

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
                                                <x-input label="{{ __('CIN') }}" name="cin"
                                                         :value="old('cin', $customer->cin)"
                                                />
                                            </div>

                                            <div class="col-sm-6 col-md-6">
                                                <x-input label="{{ __('Date of Birth') }}" name="date_of_birth"
                                                         type="date"
                                                         :value="old('date_of_birth', $customer->date_of_birth?->format('Y-m-d'))"
                                                />
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

@pushonce('page-scripts')
    <script src="{{ asset('assets/js/img-preview.js') }}"></script>
@endpushonce
