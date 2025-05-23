<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ __('Repair Tickets') }}</h3>
            <div class="card-actions">
                <a href="{{ route('repair-tickets.create') }}" class="btn btn-primary">
                    {{ __('Create') }}
                </a>
            </div>
        </div>

        <div class="card-body border-bottom py-3">
            <div class="d-flex">
                <div class="text-secondary">
                    Show
                    <div class="mx-2 d-inline-block">
                        <select class="form-select" wire:model.live="perPage">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                    entries
                </div>
                <div class="ms-auto text-secondary">
                    Search:
                    <div class="ms-2 d-inline-block">
                        <input type="text" class="form-control" wire:model.live="search">
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered card-table table-vcenter text-nowrap datatable">
                <thead>
                <tr>
                    <th wire:click="sortBy('ticket_number')" class="cursor-pointer">
                        {{ __('Ticket') }}
{{--                        @include('includes._sort-icon', ['field' => 'ticket_number'])--}}
                    </th>
                    <th wire:click="sortBy('customer_id')" class="cursor-pointer">
                        {{ __('Customer / Driver') }}
{{--                        @include('includes._sort-icon', ['field' => 'customer_id'])--}}
                    </th>
                    <th>{{ __('Product') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Technician') }}</th>
                    <th>{{ __('Created Date') }}</th>
                    <th class="w-1">{{ __('Actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($tickets as $ticket)
                    <tr>
                        <td>{{ $ticket->ticket_number }}</td>
                        <td>
                            @if($ticket->brought_by === 'customer')
                                {{ $ticket->customer?->name }}
                                <span class="badge bg-green-lt">{{ __('Customer') }}</span>
                            @else
                                {{ $ticket->driver?->name }}
                                <span class="badge bg-blue-lt">{{ __('Driver') }}</span>
                            @endif
                        </td>
                        <td>{{ $ticket->product?->name }}</td>
                        <td>
                            <span @class([
                                'badge',
                                'bg-success' => $ticket->status === 'REPAIRED',
                                'bg-danger' => $ticket->status === 'UNREPAIRABLE',
                                'bg-warning' => $ticket->status === 'IN_PROGRESS',
                                'bg-info' => $ticket->status === 'RECEIVED',
                                'bg-primary' => $ticket->status === 'DELIVERED',
                            ])
                            style="color: white !important;">
                                @if($ticket->status === 'RECEIVED')
                                    {{ __('Received') }}
                                @elseif($ticket->status === 'IN_PROGRESS')
                                    {{ __('In Progress') }}
                                @elseif($ticket->status === 'REPAIRED')
                                    {{ __('Repaired') }}
                                @elseif($ticket->status === 'UNREPAIRABLE')
                                    {{ __('Unrepairable') }}
                                @elseif($ticket->status === 'DELIVERED')
                                    {{ __('Delivered') }}
                                @else
                                    {{ $ticket->status }}
                                @endif
                            </span>
                        </td>
                        <td>{{ $ticket->technician?->name ?? '-' }}</td>
                        <td>{{ $ticket->created_at->format('d M Y H:i') }}</td>
                        <td>
                            <div class="btn-list flex-nowrap">
                                <a href="{{ route('repair-tickets.show', $ticket) }}" class="btn btn-primary btn-sm">
                                    {{ __('View') }}
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">{{ __('No records found') }}</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex align-items-center">
            <p class="m-0 text-secondary">
                {{ __('Showing') }} <span>{{ $tickets->firstItem() }}</span>
                {{ __('to') }} <span>{{ $tickets->lastItem() }}</span>
                {{ __('of') }} <span>{{ $tickets->total() }}</span> {{ __('entries') }}
            </p>

            <div class="m-0 ms-auto">
                {{ $tickets->links() }}
            </div>
        </div>
    </div>
</div>
