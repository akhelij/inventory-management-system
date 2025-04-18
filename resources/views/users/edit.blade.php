@extends('layouts.tabler')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center mb-3">
                <div class="col">
                    <h2 class="page-title">
                        {{ __('Edit') }}
                    </h2>
                </div>
            </div>

            @include('partials._breadcrumbs', ['model' => $user])
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">

                <div class="col-lg-4">
                    <div class="row row-cards">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title">
                                        {{ __('Image') }}
                                    </h3>

                                    <img class="img-account-profile rounded-circle mb-2" src="{{ $user->photo ? asset('storage/profile/'.$user->photo) : asset('assets/img/demo/user-placeholder.svg') }}" alt="" id="image-preview" />

                                    <div class="small font-italic text-muted mb-2">{{ __('JPG or PNG') }}</div>

                                    <input class="form-control form-control-solid mb-2 @error('photo') is-invalid @enderror" type="file"  id="image" name="photo" accept="image/*" onchange="previewImage();">

                                    @error('photo')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="row row-cards">

                        <div class="col-12">
                            <form action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('put')

                                <div class="card">
                                    <div class="card-body">
                                        <h3 class="card-title">
                                            {{ __('User Details') }}
                                        </h3>
                                        <div class="row row-cards">
                                            <div class="col-md-12">
                                                <x-input name="name" :value="old('name', $user->name)" required="true"/>

                                                <x-input name="email" :value="old('email', $user->email)" label="{{ __('Email address') }}" required="true"/>
                                            </div>

                                            <div class="col-md-12">
                                                <label class="mb-1" for="tag">
                                                    {{ __('Warehouse') }}
                                                </label>
                                                <select class="form-select @error('warehouse_id') is-invalid @enderror" name="warehouse_id">
                                                    <option value="">{{ __('ALL') }}</option>
                                                    @foreach($warehouses as $warehouse)
                                                        <option value="{{ $warehouse->id }}" {{ old('warehouse_id', $user->warehouse_id) == $warehouse->id ? 'selected' : '' }}>
                                                            {{ $warehouse->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer text-end">
                                        <button type="submit" class="btn btn-primary">
                                            {{ __('Save') }}
                                        </button>

                                        <a class="btn btn-outline-warning" href="{{ route('users.index') }}">
                                            {{ __('Cancel') }}
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="col-12">
                            <form action="{{ route('users.updatePassword', $user) }}" method="POST">
                                @csrf
                                @method('put')

                                <div class="card">
                                    <div class="card-body">
                                        <h3 class="card-title">
                                            {{ __('Change Password') }}
                                        </h3>

                                        <div class="row row-cards">
                                            <div class="col-sm-6 col-md-6">
                                                <x-input type="password" name="password"/>
                                            </div>

                                            <div class="col-sm-6 col-md-6">
                                                <x-input type="password" name="password_confirmation" label="{{ __('Password Confirmation') }}"/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-footer text-end">
                                        <button type="submit" class="btn btn-primary">
                                            {{ __('Save') }}
                                        </button>

                                        <a class="btn btn-outline-warning" href="{{ route('users.index') }}">
                                            {{ __('Cancel') }}
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@pushonce('page-scripts')
    <script src="{{ asset('assets/js/img-preview.js') }}"></script>
@endpushonce
