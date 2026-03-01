<div>
    <div class="card">
        <div class="card-header d-flex flex-column flex-md-row align-items-md-center">
            <div>
                <h3 class="card-title">
                    {{ $category === 'b2c' ? __('Clients') : __('Customers') }}
                </h3>
                <p class="text-muted mt-1 mb-0">{{ $category === 'b2c' ? __('Manage your client database') : __('Manage your customer database') }}</p>
            </div>

            <div class="card-actions ms-auto mt-3 mt-md-0">
                <x-button.print class="btn-icon" route="{{ route('payments.export') }}"/>
                <x-action.create route="{{ route('customers.create', ['category' => $category ?: null]) }}" />
            </div>
        </div>

        <!-- Filter Options -->
        <div class="card-body border-bottom pt-0 pb-3">
            <div class="row mt-3">
                <div class="col-12">
                    <div class="filter-status-badges">
                        @if(request()->has('only_unpaid') || request()->has('only_out_of_limit'))
                            <div class="d-flex align-items-center">
                                <div class="alert alert-info d-flex align-items-center p-3 mb-0 me-3">
                                    <i class="fas fa-filter me-2"></i>
                                    <span>
                                        @if(request()->has('only_unpaid'))
                                            {{ __('Showing customers with unpaid checks') }}
                                        @elseif(request()->has('only_out_of_limit'))
                                            {{ __('Showing customers exceeding credit limit') }}
                                        @endif
                                    </span>
                                    <a href="{{ route('customers.index', ['category' => $category ?: null]) }}" class="ms-2 btn btn-sm btn-outline-info">
                                        <i class="fas fa-times"></i> {{ __('Clear filter') }}
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('customers.index', ['category' => $category ?: null, 'only_out_of_limit' => 1]) }}" class="status-filter-card p-2 text-decoration-none">
                                    <div class="d-flex align-items-center">
                                        <div class="status-icon bg-warning p-2 rounded-circle me-2">
                                            <i class="fas fa-exclamation-triangle text-white"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ __('Credit Limit Exceeded') }}</h6>
                                            <small class="text-muted">{{ __('View customers over their credit threshold') }}</small>
                                        </div>
                                    </div>
                                </a>
                                
                                <a href="{{ route('customers.index', ['category' => $category ?: null, 'only_unpaid' => 1]) }}" class="status-filter-card p-2 text-decoration-none">
                                    <div class="d-flex align-items-center">
                                        <div class="status-icon bg-danger p-2 rounded-circle me-2">
                                            <i class="fas fa-file-invoice-dollar text-white"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ __('Unpaid Checks') }}</h6>
                                            <small class="text-muted">{{ __('View customers with outstanding payments') }}</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body border-bottom py-3">
            <div class="d-flex">
                <div class="text-secondary">
                    {{ __('Showing') }}
                    <div class="mx-2 d-inline-block">
                        <select wire:model.live="perPage" class="form-select form-select-sm" aria-label="result per page">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="15">15</option>
                            <option value="25">25</option>
                        </select>
                    </div>
                    {{ __('entries') }}
                </div>
                <div class="ms-auto text-secondary">
                    {{ __('Search') }}
                    <div class="ms-2 d-inline-block">
                        <input type="text" wire:model.live="search" class="form-control form-control-sm"
                            aria-label="{{ __('Search') }}">
                    </div>
                </div>
            </div>
        </div>

        <x-spinner.loading-spinner/>

        <div class="table-responsive">
            <table wire:loading.remove class="table card-table table-vcenter text-nowrap datatable">
                <thead class="thead-light">
                <tr>
                    <th scope="col" class="align-middle text-center">
                        <a wire:click.prevent="sortBy('id')" href="#" role="button">
                            {{ __('Id') }}
                            @include('inclues._sort-icon', ['field' => 'id'])
                        </a>
                    </th>
                    <th scope="col" class="align-middle text-center">
                        <a wire:click.prevent="sortBy('name')" href="#" role="button">
                            {{ __('Name') }}
                            @include('inclues._sort-icon', ['field' => 'name'])
                        </a>
                    </th>
                    <th scope="col" class="align-middle text-center">
                        <a wire:click.prevent="sortBy('email')" href="#" role="button">
                            {{ __('Email') }}
                            @include('inclues._sort-icon', ['field' => 'email'])
                        </a>
                    </th>
                    <th scope="col" class="align-middle text-center">
                        <a wire:click.prevent="sortBy('user_id')" href="#" role="button">
                            {{ __('Author') }}
                            @include('inclues._sort-icon', ['field' => 'user_id'])
                        </a>
                    </th>
                    <th scope="col" class="align-middle text-center">
                        <a wire:click.prevent="sortBy('created_at')" href="#" role="button">
                            {{ __('Created at') }}
                            @include('inclues._sort-icon', ['field' => 'Created_at'])
                        </a>
                    </th>
                    <th scope="col" class="align-middle text-center">
                        {{ __('Action') }}
                    </th>
                </tr>
                </thead>
                <tbody>
                @forelse ($customers as $customer)
                    <tr>
                        <td class="align-middle text-center">
                            {{ $customer->id }}
                        </td>
                        <td class="align-middle text-center">
                            {{ $customer->name }}
                            @if(isset($customer->is_out_of_limit) && $customer->is_out_of_limit)
                                <span class="badge bg-warning ms-1" title="{{ __('Exceeding credit limit') }}">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </span>
                            @endif
                            @if(isset($customer->has_unpaid) && $customer->has_unpaid)
                                <span class="badge bg-danger ms-1" title="{{ __('Has unpaid checks') }}">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                </span>
                            @endif
                        </td>
                        <td class="align-middle text-center">
                            {{ $customer->email }}
                        </td>
                        <td class="align-middle text-center">
                            {{ $customer->user?->name ?? '--' }}
                        </td>
                        <td class="align-middle text-center">
                            {{ $customer->created_at->diffForHumans() }}
                        </td>
                        <td class="align-middle text-center">
                            <x-button.show class="btn-icon" route="{{ route('customers.show', $customer->uuid) }}"/>
                            <x-button.edit class="btn-icon" route="{{ route('customers.edit', $customer->uuid) }}"/>
                            <x-button.delete class="btn-icon" route="{{ route('customers.destroy', $customer->uuid) }}" onclick="return confirm('are you sure!')"/>
                        </td>
                    </tr>
                @empty
                    <tr>
                            <td class="align-middle text-center" colspan="7">
                                {{ __('No results found') }}
                            </td>
                        </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex align-items-center">
            <p class="m-0 text-secondary">
                {{ __('Showing') }} <span>{{ $customers->firstItem() }}</span> {{ __('to') }} <span>{{ $customers->lastItem() }}</span> {{ __('of') }} <span>{{ $customers->total() }}</span> {{ __('entries') }}
            </p>

            <ul class="pagination m-0 ms-auto">
                {{ $customers->links() }}
            </ul>
        </div>
    </div>

    <style>
        .status-filter-card {
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }
        
        .status-filter-card:hover {
            background-color: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .status-icon {
            height: 32px;
            width: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        [data-bs-theme="dark"] .status-filter-card {
            border-color: #2c3e50;
            background-color: #1e293b;
        }
        
        [data-bs-theme="dark"] .status-filter-card:hover {
            background-color: #2c3e50;
        }
    </style>
</div>
