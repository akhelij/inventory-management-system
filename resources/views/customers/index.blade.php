@extends('layouts.tabler')

@section('content')
<div class="page-body">
    @if(!$customers)
        <x-empty
            title="{{ ($category ?? '') === 'b2c' ? __('No clients found') : __('No customers found') }}"
            message="Try adjusting your search or filter to find what you're looking for."
            button_label="{{ ($category ?? '') === 'b2c' ? __('Add your first Client') : __('Add your first Customer') }}"
            button_route="{{ route('customers.create', ['category' => $category ?? 'b2b']) }}"
        />
    @else
        <div class="container-xl">
            @livewire('tables.customer-table', ['category' => $category ?? ''])
        </div>
    @endif
</div>
@endsection
