@extends('layouts.auth')

@section('content')
    <form class="card card-md" action="{{ route('password.store') }}" method="POST" autocomplete="off">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="card-body">
            <h2 class="card-title text-center mb-4">
                {{ __('Reset Password') }}
            </h2>
            <div class="mb-3">
                <label for="email" class="form-label">{{ __('Email address') }}</label>
                <input type="email" name="email" id="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', $request->email) }}"
                       placeholder="{{ __('Enter email') }}"
                >

                @error('email')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">{{ __('New Password') }}</label>
                <div class="input-group input-group-flat">
                    <input type="password" name="password" id="password"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="{{ __('Password') }}"
                           autocomplete="off"
                    >

                    @error('password')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
                <div class="input-group input-group-flat">
                    <input type="password" name="password_confirmation" id="password_confirmation"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="{{ __('Password Confirmation') }}"
                           autocomplete="off"
                    >

                    @error('password')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
            </div>
            <div class="form-footer">
                <button type="submit" class="btn btn-primary w-100">
                    {{ __('Reset Password') }}
                </button>
            </div>
        </div>
    </form>
@endsection
