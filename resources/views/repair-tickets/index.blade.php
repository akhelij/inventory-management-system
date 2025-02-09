@extends('layouts.tabler')

@section('content')
    <div class="page-body">
        @if (!$tickets->count())
            <x-empty
                title="No repair tickets found"
                message="Try adjusting your search or filter to find what you're looking for."
                button_label="{{ __('Create Repair Ticket') }}"
                button_route="{{ route('repair-tickets.create') }}"
            />
        @else
            <div class="container-xl">
                <x-alert />
                @livewire('tables.repair-ticket-table')
            </div>
        @endif
    </div>
@endsection
