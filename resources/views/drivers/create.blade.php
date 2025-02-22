@extends('layouts.tabler')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">
                            {{ __('Create Driver') }}
                        </h3>
                    </div>
                    <div class="card-actions">
                        <x-action.close route="{{ route('drivers.index') }}" />
                    </div>
                </div>
                <form method="POST" action="{{ route('drivers.store') }}">
                    @csrf
                    <div class="card-body">
                        <x-input
                            label="{{ __('Name') }}"
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            required
                        />
                        <x-input
                            label="{{ __('Phone') }}"
                            id="phone"
                            name="phone"
                            value="{{ old('phone') }}"
                        />
                        <x-input
                            label="{{ __('License Number') }}"
                            id="license_number"
                            name="license_number"
                            value="{{ old('license_number') }}"
                        />
                    </div>
                    <div class="card-footer text-end">
                        <x-button type="submit">
                            {{ __('Create') }}
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
