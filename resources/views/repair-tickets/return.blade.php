@extends('layouts.tabler')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center mb-3">
                <div class="col">
                    <h2 class="page-title">
                        {{ __('Process Return') }} #{{ $repairTicket->ticket_number }}
                    </h2>
                    <span @class([
                        'badge',
                        'bg-success' => $repairTicket->status === 'REPAIRED',
                        'bg-danger' => $repairTicket->status === 'UNREPAIRABLE',
                    ])>
                        {{ $repairTicket->status }}
                    </span>
                </div>

                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="{{ route('repair-tickets.show', $repairTicket) }}" class="btn btn-outline-secondary">
                            {{ __('Cancel') }}
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
                <div class="col-12">
                    <x-repair-status-stepper :currentStatus="$repairTicket->status" />
                </div>
                
                <div class="col-lg-8">
                    <form action="{{ route('repair-tickets.process-return', $repairTicket) }}" method="POST" enctype="multipart/form-data" class="card">
                        @csrf
                        @method('PUT')
                        
                        <div class="card-header">
                            <h3 class="card-title">{{ __('Return Process') }}</h3>
                        </div>
                        
                        <div class="card-body">
                            <!-- Final photos -->
                            <div class="mb-3">
                                <label class="form-label">{{ __('Return Condition Photos') }}</label>
                                <input type="file" name="return_photos[]" class="form-control" multiple>
                                <small class="form-hint">{{ __('Optional - document the condition upon return') }}</small>
                            </div>
                            
                            <!-- Who is collecting -->
                            <div class="mb-3">
                                <label class="form-label required">{{ __('Collected By') }}</label>
                                <div class="form-selectgroup">
                                    <label class="form-selectgroup-item">
                                        <input type="radio" name="collected_by" value="customer" class="form-selectgroup-input" 
                                               {{ $repairTicket->brought_by === 'customer' ? 'checked' : '' }}>
                                        <span class="form-selectgroup-label">
                                            {{ __('Customer') }}
                                            @if($repairTicket->brought_by === 'customer')
                                                ({{ $repairTicket->customer->name }})
                                            @endif
                                        </span>
                                    </label>
                                    <label class="form-selectgroup-item">
                                        <input type="radio" name="collected_by" value="driver" class="form-selectgroup-input"
                                               {{ $repairTicket->brought_by === 'driver' ? 'checked' : '' }}>
                                        <span class="form-selectgroup-label">
                                            {{ __('Driver') }}
                                            @if($repairTicket->brought_by === 'driver')
                                                ({{ $repairTicket->driver->name }})
                                            @endif
                                        </span>
                                    </label>
                                    <label class="form-selectgroup-item">
                                        <input type="radio" name="collected_by" value="other" class="form-selectgroup-input">
                                        <span class="form-selectgroup-label">{{ __('Other') }}</span>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Other collector info (conditionally shown) -->
                            <div class="mb-3 other-collector-info" style="display: none;">
                                <label class="form-label required">{{ __('Collector Name') }}</label>
                                <input type="text" name="collector_name" class="form-control" placeholder="{{ __('Enter the name of the person collecting this item') }}">
                            </div>
                        </div>
                        
                        <div class="card-footer text-end">
                            <button type="submit" class="btn btn-primary">{{ __('Complete Return Process') }}</button>
                        </div>
                    </form>
                </div>
                
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">{{ __('Ticket Information') }}</h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">{{ __('Product') }}</div>
                                    <div class="datagrid-content">{{ $repairTicket->product->name }}</div>
                                </div>
                                
                                <div class="datagrid-item">
                                    <div class="datagrid-title">{{ __('Serial Number') }}</div>
                                    <div class="datagrid-content">{{ $repairTicket->serial_number ?? '-' }}</div>
                                </div>
                                
                                <div class="datagrid-item">
                                    <div class="datagrid-title">{{ __('Received Date') }}</div>
                                    <div class="datagrid-content">{{ $repairTicket->created_at->format('d M Y') }}</div>
                                </div>
                                
                                <div class="datagrid-item">
                                    <div class="datagrid-title">{{ __('Brought By') }}</div>
                                    <div class="datagrid-content">
                                        @if($repairTicket->brought_by === 'customer')
                                            {{ $repairTicket->customer->name }}
                                            <span class="badge bg-green-lt">{{ __('Customer') }}</span>
                                        @else
                                            {{ $repairTicket->driver->name }}
                                            <span class="badge bg-blue-lt">{{ __('Driver') }}</span>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="datagrid-item">
                                    <div class="datagrid-title">{{ __('Technician') }}</div>
                                    <div class="datagrid-content">{{ $repairTicket->technician->name ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const collectedByInputs = document.querySelectorAll('input[name="collected_by"]');
            const otherCollectorInfo = document.querySelector('.other-collector-info');
            
            function toggleOtherCollectorInfo() {
                const selectedValue = document.querySelector('input[name="collected_by"]:checked').value;
                otherCollectorInfo.style.display = selectedValue === 'other' ? 'block' : 'none';
            }
            
            collectedByInputs.forEach(input => {
                input.addEventListener('change', toggleOtherCollectorInfo);
            });
            
            // Initial toggle
            toggleOtherCollectorInfo();
        });
    </script>
    @endpush
@endsection 