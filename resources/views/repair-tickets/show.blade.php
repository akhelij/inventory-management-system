@extends('layouts.tabler')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center mb-3">
                <div class="col">
                    <h2 class="page-title">
                        {{ __('Repair Ticket') }} #{{ $repairTicket->ticket_number }}
                    </h2>
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
                    <span class="text-muted">
                            {{ __('Created by') }}: {{ $repairTicket->creator->name }}
                        </span>
                </div>

                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="{{ url()->previous() }}" class="btn btn-secondary d-none d-sm-inline-block">
                            {{ __('Back') }}
                        </a>
                        @if($repairTicket->status !== 'DELIVERED')
                        <a href="{{ route('repair-tickets.edit', $repairTicket) }}" class="btn btn-primary d-none d-sm-inline-block">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-pencil" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" />
                                <path d="M13.5 6.5l4 4" />
                            </svg>
                            {{ __('Edit') }}
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            @include('partials._breadcrumbs')
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row mb-3">
                <div class="col-12">
                    <x-repair-status-stepper :currentStatus="$repairTicket->status" />
                </div>
            </div>
            
            <div class="row row-cards">
                <div class="col-lg-4">
                    @if($repairTicket->status !== 'DELIVERED')
                        <x-repair-actions-card :repairTicket="$repairTicket" />
                    @endif

                    <div class="card mt-3">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('Photos') }}</h3>
                            <div class="card-actions">
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#photoUploadModal">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-plus" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M12 5l0 14" />
                                        <path d="M5 12l14 0" />
                                    </svg>
                                    {{ __('Add Photos') }}
                                </button>
                            </div>
                            </div>
                            <div class="card-body">
                            @if($repairTicket->photos->count() > 0)
                                <div class="row g-2">
                                    @foreach($repairTicket->photos as $photo)
                                        <div class="col-6">
                                            <div class="position-relative">
                                                <a href="{{ asset('storage/' . $photo->photo_path) }}" target="_blank" class="d-block">
                                            <img src="{{ asset('storage/' . $photo->photo_path) }}"
                                                 class="img-fluid rounded"
                                                 alt="Repair photo">
                                                </a>
                                                <form action="{{ route('repair-tickets.delete-photo', $photo->id) }}" method="POST" class="position-absolute" style="top: 5px; right: 5px;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('{{ __('Are you sure you want to delete this photo?') }}')">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-trash" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                            <path d="M4 7l16 0" />
                                                            <path d="M10 11l0 6" />
                                                            <path d="M14 11l0 6" />
                                                            <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                                            <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                                        </svg>
                                                    </button>
                                                </form>
                                                <div class="text-muted small mt-1">
                                                    {{ $photo->photo_type ? ucfirst($photo->photo_type) : 'Product' }} photo
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="empty">
                                    <div class="empty-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-photo-off" width="40" height="40" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M15 8h.01" />
                                            <path d="M19.121 19.122a3 3 0 0 1 -2.121 .878h-10a3 3 0 0 1 -3 -3v-10c0 -.833 .34 -1.587 .888 -2.131m3.112 -.869h9a3 3 0 0 1 3 3v9" />
                                            <path d="M4 15l4 -4c.928 -.893 2.072 -.893 3 0l5 5" />
                                            <path d="M16.32 12.34c.577 -.059 1.162 .162 1.68 .66l2 2" />
                                            <path d="M3 3l18 18" />
                                        </svg>
                                    </div>
                                    <p class="empty-title">{{ __('No photos uploaded') }}</p>
                                    <p class="empty-subtitle text-muted">
                                        {{ __('Click the Add Photos button to upload images of the repair.') }}
                                    </p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                @if($repairTicket->brought_by === 'customer')
                                    {{ __('Customer Information') }}
                                @else
                                    {{ __('Driver Information') }}
                                @endif
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">{{ __('Name') }}</div>
                                    <div class="datagrid-content">
                                        <div class="d-flex align-items-center">
                                            @if($repairTicket->brought_by === 'customer')
                                                <span class="avatar avatar-xs me-2 rounded">
                                                    {{ strtoupper(substr($repairTicket->customer?->name ?? '', 0, 2)) }}
                                                </span>
                                                {{ $repairTicket->customer?->name }}
                                            @else
                                                <span class="avatar avatar-xs me-2 rounded bg-blue">
                                                    {{ strtoupper(substr($repairTicket->driver?->name ?? '', 0, 2)) }}
                                                </span>
                                                {{ $repairTicket->driver?->name }}
                                                <span class="badge bg-blue ms-2">{{ __('Driver') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="datagrid-item">
                                    <div class="datagrid-title">{{ __('Phone') }}</div>
                                    <div class="datagrid-content">
                                        @if($repairTicket->brought_by === 'customer')
                                            {{ $repairTicket->customer?->phone }}
                                        @else
                                            {{ $repairTicket->driver?->phone }}
                                        @endif
                                    </div>
                                </div>

                                @if($repairTicket->brought_by === 'customer')
                                    <div class="datagrid-item">
                                        <div class="datagrid-title">{{ __('Address') }}</div>
                                        <div class="datagrid-content">{{ $repairTicket->customer->address ?? '-' }}</div>
                                    </div>
                                @else
                                    <div class="datagrid-item">
                                        <div class="datagrid-title">{{ __('License Number') }}</div>
                                        <div class="datagrid-content">{{ $repairTicket->driver->license_number ?? '-' }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title">{{ __('Product') . ' : ' . $repairTicket->product->name }}</h3>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    @if($repairTicket->product->product_image)
                                        <img src="{{ Storage::url($repairTicket->product->product_image) }}"
                                             alt="{{ $repairTicket->product->name }}"
                                             class="rounded"
                                             style="max-width: 100px;">
                                    @else
                                        <span class="avatar avatar-lg">{{ strtoupper(substr($repairTicket->product->name, 0, 2)) }}</span>
                                    @endif
                                </div>
                                <div class="col">
                                    <div class="datagrid">
                                        <div class="datagrid-item">
                                            <div class="datagrid-title">{{ __('Reference') }}</div>
                                            <div class="datagrid-content">{{ $repairTicket->product->code }}</div>
                                        </div>

                                        <div class="datagrid-item">
                                            <div class="datagrid-title">{{ __('Serial Number') }}</div>
                                            <div class="datagrid-content">{{ $repairTicket->serial_number ?? '-' }}</div>
                                        </div>
                                        <div class="datagrid-item w-100">
                                            <div class="datagrid-title">{{ __('Problem Description') }}</div>
                                            <div class="datagrid-content">{{ $repairTicket->problem_description ?? '--' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="card">
                        <div class="card-header bg-primary-subtle">
                            <h3 class="card-title text-primary">{{ __('Status Timeline') }}</h3>
                        </div>
                        <div class="card-body p-2">
                            <ul class="timeline timeline-compact">
                                @foreach($repairTicket->statusHistories->sortByDesc('created_at') as $history)
                                    <li class="timeline-event">
                                        <div class="timeline-event-icon bg-{{ 
                                            $history->to_status === 'RECEIVED' ? 'info' : 
                                            ($history->to_status === 'IN_PROGRESS' ? 'warning' : 
                                            ($history->to_status === 'REPAIRED' ? 'success' : 
                                            ($history->to_status === 'UNREPAIRABLE' ? 'danger' : 
                                            ($history->to_status === 'DELIVERED' ? 'primary' : 'secondary')))) 
                                        }}">
                                            @if($history->to_status === 'RECEIVED')
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-package" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                    <path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5" />
                                                    <path d="M12 12l8 -4.5" />
                                                    <path d="M12 12l0 9" />
                                                    <path d="M12 12l-8 -4.5" />
                                                </svg>
                                            @elseif($history->to_status === 'IN_PROGRESS')
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-tools" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                    <path d="M3 21h4l13 -13a1.5 1.5 0 0 0 -4 -4l-13 13v4" />
                                                    <path d="M14.5 5.5l4 4" />
                                                    <path d="M12 8l-5 -5l-4 4l5 5" />
                                                    <path d="M7 8l-1.5 1.5" />
                                                    <path d="M16 12l5 5l-4 4l-5 -5" />
                                                    <path d="M16 17l-1.5 1.5" />
                                                </svg>
                                            @elseif($history->to_status === 'REPAIRED')
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-check" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                    <path d="M5 12l5 5l10 -10" />
                                                </svg>
                                            @elseif($history->to_status === 'UNREPAIRABLE')
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-x" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                    <path d="M18 6l-12 12" />
                                                    <path d="M6 6l12 12" />
                                                </svg>
                                            @elseif($history->to_status === 'DELIVERED')
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrow-forward" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                    <path d="M15 11l4 4l-4 4m4 -4h-11a4 4 0 0 1 0 -8h1" />
                                                </svg>
                                            @endif
                                        </div>
                                        <div class="card timeline-event-card">
                                            <div class="card-body p-2">
                                                <div class="d-flex align-items-center">
                                                    <span class="status-dot status-dot-animated status-{{ 
                                                        $history->to_status === 'RECEIVED' ? 'blue' : 
                                                        ($history->to_status === 'IN_PROGRESS' ? 'yellow' : 
                                                        ($history->to_status === 'REPAIRED' ? 'green' : 
                                                        ($history->to_status === 'UNREPAIRABLE' ? 'red' : 
                                                        ($history->to_status === 'DELIVERED' ? 'azure' : 'gray')))) 
                                                    }} me-2"></span>
                                                    <strong>{{ $history->to_status }}</strong>
                                                </div>
                                                <div class="text-muted small mt-1">
                                                    {{ $history->created_at->format('d M Y, H:i') }}
                                                </div>
                                                <div class="text-muted small">
                                                    {{ __('by') }} {{ $history->user->name }}
                                                </div>
                                                
                                                @if($history->comment)
                                                    @if(is_array(json_decode($history->comment, true)))
                                                        @php
                                                            $statusDetails = json_decode($history->comment, true);
                                                        @endphp
                                                        <div class="mt-2">
                                                            @if($history->to_status === 'REPAIRED')
                                                                <strong>{{ __('Resolution Details') }}:</strong>
                                                                <p class="text-muted small mb-1">{{ $statusDetails['resolution_details'] }}</p>
                                                                
                                                                @if(!empty($statusDetails['parts_replaced']))
                                                                    <strong>{{ __('Parts Replaced') }}:</strong>
                                                                    <p class="text-muted small mb-1">{{ $statusDetails['parts_replaced'] }}</p>
                                                                @endif
                                                                
                                                                @if(!empty($statusDetails['comment']))
                                                                    <strong>{{ __('Comment') }}:</strong>
                                                                    <p class="text-muted small mb-0">{{ $statusDetails['comment'] }}</p>
                                                                @endif
                                                            @elseif($history->to_status === 'UNREPAIRABLE')
                                                                <strong>{{ __('Problem Description') }}:</strong>
                                                                <p class="text-muted small mb-1">{{ $statusDetails['problem_description'] }}</p>
                                                                
                                                                @if(!empty($statusDetails['comment']))
                                                                    <strong>{{ __('Comment') }}:</strong>
                                                                    <p class="text-muted small mb-0">{{ $statusDetails['comment'] }}</p>
                                                                @endif
                                                            @elseif($history->to_status === 'DELIVERED')
                                                                <strong>{{ __('Collected By') }}:</strong>
                                                                <p class="text-muted small mb-0">
                                                                    @if($statusDetails['collected_by'] === 'customer')
                                                                        {{ __('Customer') }}: 
                                                                        @if(isset($statusDetails['collector_info']))
                                                                            {{ $statusDetails['collector_info'] }}
                                                                        @else
                                                                            {{ \App\Models\Customer::find($statusDetails['customer_id'])->name ?? __('Unknown') }}
                                                                        @endif
                                                                    @elseif($statusDetails['collected_by'] === 'driver')
                                                                        {{ __('Driver') }}: 
                                                                        @if(isset($statusDetails['collector_info']))
                                                                            {{ $statusDetails['collector_info'] }}
                                                                        @else
                                                                            {{ \App\Models\Driver::find($statusDetails['driver_id'])->name ?? __('Unknown') }}
                                                                        @endif
                                                                    @else
                                                                        {{ __('Other') }}: {{ $statusDetails['collector_name'] }}
                                                                    @endif
                                                                </p>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <p class="text-muted small">{{ $history->comment }}</p>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Photo Upload Modal -->
            <div class="modal modal-blur fade" id="photoUploadModal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <form action="{{ route('repair-tickets.upload-photos', $repairTicket) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">{{ __('Upload Photos') }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label required">{{ __('Photos') }}</label>
                                    <input type="file" name="photos[]" class="form-control" multiple required>
                                    <small class="form-hint">{{ __('You can select multiple photos. Maximum 5MB per file.') }}</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Photo Type') }}</label>
                                    <select name="photo_type" class="form-select">
                                        <option value="">{{ __('Product photo (default)') }}</option>
                                        <option value="damage">{{ __('Damage photo') }}</option>
                                        <option value="repair">{{ __('Repair process photo') }}</option>
                                        <option value="return">{{ __('Return condition photo') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                <button type="submit" class="btn btn-primary">{{ __('Upload') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Include the return modal component -->
    @if($repairTicket->status === 'REPAIRED' || $repairTicket->status === 'UNREPAIRABLE')
        <x-repair-return-modal :repairTicket="$repairTicket" />
    @endif
@endsection

<style>
    /* Timeline styling */
    .timeline {
        position: relative;
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .timeline-event {
        position: relative;
        padding-bottom: 1.5rem;
        padding-left: 2.5rem;
    }
    
    .timeline-event:before {
        content: "";
        position: absolute;
        left: 0.85rem;
        top: 2rem;
        bottom: 0;
        width: 2px;
        background: #e6e7e9;
    }
    
    .timeline-event:last-child:before {
        display: none;
    }
    
    .timeline-event-icon {
        position: absolute;
        left: 0;
        top: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 1.75rem;
        height: 1.75rem;
        color: #ffffff;
        border-radius: 50%;
    }
    
    .timeline-event-card {
        position: relative;
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    /* Compact timeline for narrow column */
    .timeline-compact .timeline-event {
        padding-bottom: 1rem;
        padding-left: 2rem;
    }
    
    .timeline-compact .timeline-event-icon {
        width: 1.5rem;
        height: 1.5rem;
    }
    
    .timeline-compact .timeline-event-icon svg {
        width: 1rem;
        height: 1rem;
    }
    
    .timeline-compact .timeline-event:before {
        left: 0.7rem;
    }
    
    .timeline-compact .timeline-event-card {
        margin-bottom: 0;
    }
    
    .timeline-compact .card-body {
        padding: 0.5rem 0.75rem;
    }
    
    /* Status dot animation */
    .status-dot {
        display: inline-block;
        width: 0.5rem;
        height: 0.5rem;
        border-radius: 50%;
    }
    
    .status-dot-animated {
        position: relative;
    }
    
    .status-dot-animated:before {
        content: '';
        display: block;
        position: absolute;
        top: -0.25rem;
        left: -0.25rem;
        right: -0.25rem;
        bottom: -0.25rem;
        border-radius: 50%;
        border: 2px solid;
        border-color: inherit;
        opacity: 0.3;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% {
            transform: scale(1);
            opacity: 0.3;
        }
        70% {
            transform: scale(1.5);
            opacity: 0;
        }
        100% {
            transform: scale(1.5);
            opacity: 0;
        }
    }
    
    .status-blue, .status-dot-blue { border-color: var(--tblr-blue); background: var(--tblr-blue); }
    .status-green, .status-dot-green { border-color: var(--tblr-green); background: var(--tblr-green); }
    .status-red, .status-dot-red { border-color: var(--tblr-red); background: var(--tblr-red); }
    .status-yellow, .status-dot-yellow { border-color: var(--tblr-yellow); background: var(--tblr-yellow); }
    .status-azure, .status-dot-azure { border-color: var(--tblr-azure); background: var(--tblr-azure); }
    .status-gray, .status-dot-gray { border-color: var(--tblr-gray); background: var(--tblr-gray); }
</style>
