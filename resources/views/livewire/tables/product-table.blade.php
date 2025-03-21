<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">
                {{ __('Products') }}
            </h3>
        </div>

        <div class="card-actions btn-group">
            <div class="dropdown">
                <a href="#" class="btn-action dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true"
                    aria-expanded="false">
                    <x-icon.vertical-dots />
                </a>
                <div class="dropdown-menu dropdown-menu-end" style="">
                    <a href="{{ route('products.create') }}" class="dropdown-item">
                        <x-icon.plus />
                        {{ __('Create product') }}
                    </a>
                    <a href="{{ route('products.import.view') }}" class="dropdown-item">
                        <x-icon.plus />
                        {{ __('Import_products') }}
                    </a>
                    <a href="{{ route('products.export.store') }}" class="dropdown-item">
                        <x-icon.plus />
                        {{ __('Export_products') }}
                    </a>
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
                {{ __('Entries') }}
            </div>
            @if ($warehouses->count() > 1 && auth()->user()->warehouse_id == null)
                <select wire:change="filterByWarehouse($event.target.value)" name="warehouse_id" id="warehouse_id"  style="width:200px;margin-left: 20px;" class="ms-auto form-control form-control-sm selector">
                    <option value=""> Choose warehouse</option>
                    @foreach ($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}">
                            {{ $warehouse->name }}
                        </option>
                    @endforeach
                </select>
            @endif
            <div class="ms-auto text-secondary">
                {{ __('Search') }}
                <div class="ms-2 d-inline-block">
                    <input type="text" wire:model.live="search" class="form-control form-control-sm"
                        aria-label="{{ __('Search') }}">
                </div>
            </div>
        </div>
    </div>

    <x-spinner.loading-spinner />

    <div class="table-responsive">
        <table wire:loading.remove class="table table-bordered card-table table-vcenter text-nowrap datatable">
            <thead class="thead-light">
                <tr>
                    <th scope="col" class="align-middle text-center">
                        {{ __('Image') }}
                    </th>

                    <th scope="col" class="align-middle text-center">
                        <a wire:click.prevent="sortBy('code')" href="#" role="button">
                            {{ __('Code') }}
                            @include('inclues._sort-icon', ['field' => 'code'])
                        </a>
                    </th>

                    <th scope="col" class="align-middle text-center">
                        <a wire:click.prevent="sortBy('name')" href="#" role="button">
                            {{ __('Name') }}
                            @include('inclues._sort-icon', ['field' => 'name'])
                        </a>
                    </th>
                    <th scope="col" class="align-middle text-center">
                        <a wire:click.prevent="sortBy('warehouse_id')" href="#" role="button">
                            {{ __('Warehouse') }}
                            @include('inclues._sort-icon', ['field' => 'warehouse_id'])
                        </a>
                    </th>
                    <th scope="col" class="align-middle text-center">
                        <a wire:click.prevent="sortBy('quantity')" href="#" role="button">
                            {{ __('quantity') }}
                            @include('inclues._sort-icon', ['field' => 'quantity'])
                        </a>
                    </th>
                    <th scope="col" class="align-middle text-center">
                        {{ __('Action') }}
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr>

                        <td class="align-middle text-center">
                            <img style="width: 90px;"
                                 src="{{ $product->product_image ? asset('storage/' . $product->product_image) : asset('assets/img/products/default.webp') }}"
                                 alt="">
                        </td>
                        <td class="align-middle text-center">
                            {{ $product->code }}
                        </td>
                        <td class="align-middle text-center">
                            {{ $product->name }}
                        </td>
                        <td class="align-middle text-center">
                            {{ $product->warehouse ? $product->warehouse->name : '--' }}
                        </td>
                        <td class="align-middle text-center">
                            {{ $product->quantity }}
                        </td>
                        <td class="align-middle text-center" style="width: 10%">
                            <x-button.show class="btn-icon" route="{{ route('products.show', $product->uuid) }}" />
                            <x-button.edit class="btn-icon" route="{{ route('products.edit', $product->uuid) }}" />
                            <x-button.delete class="btn-icon" route="{{ route('products.destroy', $product->uuid) }}"
                                onclick="return confirm('{{ __('Are you sure!') }}')" />
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
            {{ __('Showing') }} <span>{{ $products->firstItem() }}</span>
            {{ __('to') }} <span>{{ $products->lastItem() }}</span> {{ __('of') }}
            <span>{{ $products->total() }}</span> {{ __('entries') }}
        </p>

        <ul class="pagination m-0 ms-auto">
            {{ $products->links() }}
        </ul>
    </div>
</div>
