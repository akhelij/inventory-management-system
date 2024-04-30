@extends('layouts.tabler')

@section('content')
<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">
                        {{ __('Add payment') }}
                    </h3>
                </div>

                <div class="card-actions">
                    <a class="btn btn-info" href="{{ route('customers.index') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrow-left"
                             width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                             fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M5 12l14 0"/>
                            <path d="M5 12l6 6"/>
                            <path d="M5 12l6 -6"/>
                        </svg>
                        {{ __('Back') }}
                    </a>
                </div>
            </div>

            <form action="{{ route('payments.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="col-6">
                        <input type="text" name="nature">
                    </div>
                    <div class="col-6">
                        <input type="text" name="banque">
                    </div>
                    <div class="col-6">
                        <input type="text" name="echeance">
                    </div>
                    <div class="col-6">
                        <input type="text" name="amount">
                    </div>
                    <div class="col-6">
                        <input type="text" name="reported">
                    </div>
                </div>
                <div class="card-footer text-end">
                    <x-button type="submit">
                        {{ __('Add') }}
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
