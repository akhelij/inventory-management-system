@extends('layouts.tabler')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center mb-3">
                <div class="col">
                    <h2 class="page-title">
                        {{ __('Create User') }}
                    </h2>
                </div>
            </div>
            @include('partials._breadcrumbs')
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title">
                                        {{ __('Image') }}
                                    </h3>
                                    <div class="card-body text-center">
                                        <img class="img-account-profile rounded-circle mb-2"
                                             src="{{ asset('assets/img/demo/user-placeholder.svg') }}"
                                             alt=""
                                             id="image-preview"
                                        >
                                        <div class="small font-italic text-muted mb-2">
                                            {{ __('JPG or PNG no larger than 2 MB') }}
                                        </div>

                                        <input type="file"
                                               id="image"
                                               name="photo"
                                               accept="image/*"
                                               onchange="previewImage();"
                                               class="form-control @error('photo') is-invalid @enderror"
                                        >

                                        @error('photo')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title">
                                        {{ __('Create Customer') }}
                                    </h3>

                                    <div class="row row-cards">
                                        <input type="hidden" id="uuid" name="uuid" value="{{ Str::uuid() }}">
                                        <div class="row gx-3 mb-3">
                                            <div class="col-md-6">
                                                <label for="role" class="form-label">
                                                    {{ __('Role') }}
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <select class="form-control" id="role" name="role_id">
                                                    <option selected="" disabled="">Select a role:</option>
                                                    @foreach($roles as $role)
                                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                                    @endforeach
                                                </select>

                                                @error('payment_type')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label for="name" class="form-label">
                                                    {{ __('Name') }}
                                                    <span class="text-danger">*</span>
                                                </label>

                                                <input type="text"
                                                       id="name"
                                                       name="name"
                                                       class="form-control @error('name') is-invalid @enderror"
                                                       value="{{ old('name') }}"
                                                >

                                                @error('name')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>

                                        </div>
                                        <div class="col-md-12">

                                            <div class="mb-3">
                                                <label for="email" class="form-label">
                                                    {{ __('Email address') }}
                                                    <span class="text-danger">*</span>
                                                </label>

                                                <input type="text"
                                                       id="email"
                                                       name="email"
                                                       class="form-control @error('email') is-invalid @enderror"
                                                       value="{{ old('email') }}"
                                                >

                                                @error('email')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>

                                        </div>


                                        <div class="col-sm-6 col-md-6">
                                            <label for="password" class="form-label">
                                                {{ __('Password') }}
                                                <span class="text-danger">*</span>
                                            </label>

                                            <input type="password"
                                                   id="password"
                                                   name="password"
                                                   class="form-control @error('password') is-invalid @enderror"
                                            >

                                            @error('password')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>


                                        <div class="col-sm-6 col-md-6">
                                            <label for="password_confirmation" class="form-label">
                                                {{ __('Password confirmation') }}
                                                <span class="text-danger">*</span>
                                            </label>

                                            <input type="password"
                                                   id="password_confirmation"
                                                   name="password_confirmation"
                                                   class="form-control @error('password_confirmation') is-invalid @enderror"
                                            >

                                            @error('password_confirmation')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer text-end">
                                    <button class="btn btn-primary" type="submit">
                                        {{ __('Create') }}
                                    </button>

                                    <a class="btn btn-outline-warning" href="{{ route('users.index') }}">
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
