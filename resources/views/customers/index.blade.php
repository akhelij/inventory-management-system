@extends('layouts.tabler')

@section('content')
<div class="page-body">
    @if(!$customers)
        <x-empty
            title="No customers found"
            message="Try adjusting your search or filter to find what you're looking for."
            button_label="{{ __('Add your first Customer') }}"
            button_route="{{ route('customers.create') }}"
        />
    @else
        <div class="container-xl">
            @livewire('tables.customer-table')
        </div>
    @endif
</div>
@endsection
