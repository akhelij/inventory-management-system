@extends('layouts.tabler')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center mb-3">
                <div class="col">
                    <h2 class="page-title">
                        {{ __('Repair Ticket') }} #{{ $repairTicket->ticket_number }}
                    </h2>
                </div>

                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="{{ route('repair-tickets.edit', $repairTicket) }}" class="btn btn-primary d-none d-sm-inline-block">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-pencil" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" />
                                <path d="M13.5 6.5l4 4" />
                            </svg>
                            {{ __('Edit') }}
                        </a>
                    </div>
                </div>
            </div>

            @include('partials._breadcrumbs')
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">{{ __('Status History') }}</h3>
                        </div>
                        <div class="list-group list-group-flush">
                            @foreach($repairTicket->statusHistories as $history)
                                <div class="list-group-item">
                                    <div class="row align-items-center">
                                        <div class="col text-truncate">
                                            <span class="text-reset d-block">{{ $history->to_status }}</span>
                                            <div class="d-block text-muted text-truncate mt-n1">
                                                {{ $history->created_at->format('d M Y H:i') }} - {{ $history->user->name }}
                                            </div>
                                            @if($history->comment)
                                                <small class="text-muted">{{ $history->comment }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @if($repairTicket->photos->count() > 0)
                        <div class="card mt-3">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('Photos') }}</h3>
                            </div>
                            <div class="card-body">
                                <div class="row row-cards">
                                    @foreach($repairTicket->photos as $photo)
                                        <div class="col-6">
                                            <img src="{{ Storage::url($photo->photo_path) }}"
                                                 class="img-fluid rounded"
                                                 alt="Repair photo">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">{{ __('Repair Details') }}</h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">{{ __('Customer') }}</div>
                                    <div class="datagrid-content">
                                        <div class="d-flex align-items-center">
                                            <span class="avatar avatar-xs me-2 rounded">
                                                {{ strtoupper(substr($repairTicket->customer->name, 0, 2)) }}
                                            </span>
                                            {{ $repairTicket->customer->name }}
                                        </div>
                                    </div>
                                </div>

                                <div class="datagrid-item">
                                    <div class="datagrid-title">{{ __('Product') }}</div>
                                    <div class="datagrid-content">{{ $repairTicket->product->name }}</div>
                                </div>

                                <div class="datagrid-item">
                                    <div class="datagrid-title">{{ __('Serial Number') }}</div>
                                    <div class="datagrid-content">{{ $repairTicket->serial_number ?? '-' }}</div>
                                </div>

                                <div class="datagrid-item">
                                    <div class="datagrid-title">{{ __('Status') }}</div>
                                    <div class="datagrid-content">
                                        <span @class([
                                            'badge',
                                            'bg-success' => $repairTicket->status === 'REPAIRED',
                                            'bg-danger' => $repairTicket->status === 'UNREPAIRABLE',
                                            'bg-warning' => $repairTicket->status === 'IN_PROGRESS',
                                            'bg-info' => $repairTicket->status === 'RECEIVED',
                                            'bg-primary' => $repairTicket->status === 'DELIVERED',
                                        ])>
                                            {{ $repairTicket->status }}
                                        </span>
                                    </div>
                                </div>

                                <div class="datagrid-item">
                                    <div class="datagrid-title">{{ __('Technician') }}</div>
                                    <div class="datagrid-content">{{ $repairTicket->technician?->name ?? '-' }}</div>
                                </div>

                                <div class="datagrid-item">
                                    <div class="datagrid-title">{{ __('Created By') }}</div>
                                    <div class="datagrid-content">{{ $repairTicket->creator->name }}</div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <div class="datagrid-title">{{ __('Problem Description') }}</div>
                                <div class="datagrid-content">{{ $repairTicket->problem_description }}</div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <a href="{{ url()->previous() }}" class="btn btn-link">
                                {{ __('Back') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
