@extends('layouts.tabler')

@section('content')
    <div class="page-body">
        @if (!$categories)
            <x-empty title="{{ __('No categories found') }}"
                message="{{ __('Try adjusting your search or filter to find what you\'re looking for.') }}"
                button_label="{{ __('Add your first Category') }}" button_route="{{ route('categories.create') }}" />
        @else
            <div class="container-xl">
                @livewire('tables.category-table')
            </div>
        @endif
    </div>
@endsection
