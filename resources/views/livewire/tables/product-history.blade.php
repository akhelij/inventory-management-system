<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <div class="form-group">
                <label for="start_date">{{ __('Start Date') }}</label>
                <input type="date" wire:model.live="startDate" id="start_date" class="form-control">
            </div>
            <div class="form-group">
                <label for="end_date">{{ __('End Date') }}</label>
                <input type="date" wire:model.live="endDate" id="end_date" class="form-control">
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mt-3">
            <div class="col-6">
                <div class="card bg-green-lt">
                    <div class="card-body p-2">
                        <div class="d-flex align-items-center">
                            <div class="subheader">{{ __('Total Incoming') }}</div>
                        </div>
                        <div class="h1 mb-0">+{{ number_format($totalIncoming) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card bg-red-lt">
                    <div class="card-body p-2">
                        <div class="d-flex align-items-center">
                            <div class="subheader">{{ __('Total Outgoing') }}</div>
                        </div>
                        <div class="h1 mb-0">-{{ number_format($totalOutgoing) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="table-responsive" >
        <h3 class="card-title ms-3">{{ __('Stock history') }}</h3>
        <table class="table table-bordered card-table table-vcenter text-nowrap datatable" style="font-size:10px !important;">
            <thead>
            <tr>
                <th>{{ __('Old Qty') }}</th>
                <th>{{ __('New Qty') }}</th>
                <th>{{ __('Change') }}</th>
                <th>{{ __('Author') }}</th>
                <th>{{ __('Date') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($entries as $entry)
                <tr>
                    <td>{{ $entry['old_quantity'] }}</td>
                    <td>{{ $entry['new_quantity'] }}</td>
                    <td>
                        @if ($entry['difference'] > 0)
                            +{{ $entry['difference'] }}
                        @else
                            {{ $entry['difference'] }}
                        @endif
                    </td>
                    <td>{{ $entry['user'] }}</td>
                    <td>{{ $entry['date'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
