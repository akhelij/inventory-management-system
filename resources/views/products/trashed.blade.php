@extends('layouts.tabler')

@section('content')
<div class="page-body">
    <div class="container-xl">
        <x-alert/>
        
        @if($trashedProducts > 0)
            <livewire:power-grid.trashed-products-table />
        @else
            <x-empty 
                title="No trashed products found" 
                message="All products are currently active."
                button_label="{{ __('View Active Products') }}" 
                button_route="{{ route('products.index') }}" />
        @endif
    </div>
</div>
@endsection
