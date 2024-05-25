@extends('layouts.tabler')

@section('content')
<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">
                        {{ __('Edit warehouse') }}
                    </h3>
                </div>

                <div class="card-actions">
                    <x-action.close route="{{ route('warehouses.index') }}" />
                </div>
            </div>
            <form action="{{ route('warehouses.update', $warehouse->id) }}" method="POST">
                @csrf
                @method('put')
                <div class="card-body">
                    <x-input
                        label="{{ __('Name') }}"
                        id="name"
                        name="name"
                        :value="old('name', $warehouse->name)"
                        required
                    />
                </div>
                <div class="card-footer text-end">
                    <x-button type="submit">
                        {{ __('Update') }}
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
