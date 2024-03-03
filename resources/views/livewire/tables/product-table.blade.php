<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">
                {{ __('products.products') }}
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
                        {{ __('products.create_product') }}
                    </a>
                    <a href="{{ route('products.import.view') }}" class="dropdown-item">
                        <x-icon.plus />
                        {{ __('products.import_products') }}
                    </a>
                    <a href="{{ route('products.export.store') }}" class="dropdown-item">
                        <x-icon.plus />
                        {{ __('products.export_products') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card-body border-bottom py-3">
        <div class="d-flex">
            <div class="text-secondary">
                {{ __('products.showing') }}
                <div class="mx-2 d-inline-block">
                    <select wire:model.live="perPage" class="form-select form-select-sm" aria-label="result per page">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="25">25</option>
                    </select>
                </div>
                {{ __('products.entries') }}
            </div>
            <div class="ms-auto text-secondary">
                {{ __('products.search') }}
                <div class="ms-2 d-inline-block">
                    <input type="text" wire:model.live="search" class="form-control form-control-sm"
                        aria-label="{{ __('products.search') }}">
                </div>
            </div>
        </div>
    </div>

    <x-spinner.loading-spinner />

    <div class="table-responsive">
        <table wire:loading.remove class="table table-bordered card-table table-vcenter text-nowrap datatable">
            <thead class="thead-light">
                <tr>
                    <th class="align-middle text-center w-1">
                        {{ __('products.no') }}
                    </th>
                    <th scope="col" class="align-middle text-center">
                        {{ __('products.image') }}
                    </th>
                    <th scope="col" class="align-middle text-center">
                        <a wire:click.prevent="sortBy('name')" href="#" role="button">
                            {{ __('products.name') }}
                            @include('inclues._sort-icon', ['field' => 'name'])
                        </a>
                    </th>
                    <th scope="col" class="align-middle text-center">
                        <a wire:click.prevent="sortBy('code')" href="#" role="button">
                            {{ __('products.code') }}
                            @include('inclues._sort-icon', ['field' => 'code'])
                        </a>
                    </th>
                    <th scope="col" class="align-middle text-center">
                        <a wire:click.prevent="sortBy('category_id')" href="#" role="button">
                            {{ __('products.category') }}
                            @include('inclues._sort-icon', ['field' => 'category_id'])
                        </a>
                    </th>
                    <th scope="col" class="align-middle text-center">
                        <a wire:click.prevent="sortBy('quantity')" href="#" role="button">
                            {{ __('products.quantity') }}
                            @include('inclues._sort-icon', ['field' => 'quantity'])
                        </a>
                    </th>
                    <th scope="col" class="align-middle text-center">
                        {{ __('products.action') }}
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td class="align-middle text-center">
                            {{ $loop->iteration }}
                        </td>
                        <td class="align-middle text-center">
                            <img style="width: 90px;"
                                src="{{ $product->product_image ? asset('storage/' . $product->product_image) : asset('assets/img/products/default.webp') }}"
                                alt="">
                        </td>
                        <td class="align-middle text-center">
                            {{ $product->name }}
                        </td>
                        <td class="align-middle text-center">
                            {{ $product->code }}
                        </td>
                        <td class="align-middle text-center">
                            {{ $product->category ? $product->category->name : '--' }}
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
                            {{ __('products.no_results_found') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer d-flex align-items-center">
        <p class="m-0 text-secondary">
            {{ __('products.showing') }} <span>{{ $products->firstItem() }}</span>
            {{ __('products.to') }} <span>{{ $products->lastItem() }}</span> {{ __('products.of') }}
            <span>{{ $products->total() }}</span> {{ __('products.entries') }}
        </p>

        <ul class="pagination m-0 ms-auto">
            {{ $products->links() }}
        </ul>
    </div>
</div>
