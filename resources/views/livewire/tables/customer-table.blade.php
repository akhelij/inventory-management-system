<div class="card">
    @if(request()->has('only_unpaid') || request()->has('only_out_of_limit'))
        <div class="col-auto mt-2 ml-2">
        <x-button class="btn-icon px-2" route="{{ route('customers.index') }}">
            {{ __('Display All') }}
        </x-button>
        </div>
    @else
        <div class="row mt-2 ml-2">
            <div class="col-auto">
                <x-button class="btn-icon btn-warning px-2" route="{{ route('customers.index') }}?only_out_of_limit=1">
                    {{ __('Display Only Out Of Limit') }}
                </x-button>
            </div>
            <div class="col-auto">
                <x-button class="btn-icon btn-danger px-2" route="{{ route('customers.index') }}?only_unpaid=1">
                    {{ __('Display Customers with unpaid checks') }}
                </x-button>
            </div>
        </div>
    @endif

    <div class="card-header">
        <div>
            <h3 class="card-title">
                {{ __('Customers') }}
            </h3>
        </div>

        <div class="card-actions">
            <x-button.print class="btn-icon" route="{{ route('payments.export') }}"/>
            <x-action.create route="{{ route('customers.create') }}" />
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
        <table wire:loading.remove class="table table-bordered card-table table-vcenter text-nowrap datatable">
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
