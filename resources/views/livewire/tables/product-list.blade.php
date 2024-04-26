<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">
                {{ __('Products') }}
            </h3>
        </div>
        <div class="ms-auto text-secondary">
            {{ __('Search') }}
            <div class="ms-2 d-inline-block">
                <input type="text" wire:model.live="search" class="form-control form-control-sm"
                       aria-label="{{ __('Search') }}">
            </div>
        </div>
    </div>

    <x-spinner.loading-spinner/>

    <div class="table-responsive">
        <table wire:loading.remove class="table table-bordered card-table table-vcenter text-nowrap datatable">
            <thead class="thead-light">
            <tr>
                <th scope="col" class="align-middle text-center">
                    {{ __('Image') }}
                </th>
                <th scope="col" class="align-middle text-center">
                    <a wire:click.prevent="sortBy('name')" href="#" role="button">
                        {{ __('Name') }}
                        @include('inclues._sort-icon', ['field' => 'name'])
                    </a>
                </th>
                <th scope="col" class="align-middle text-center">
                    <a wire:click.prevent="sortBy('quantity')" href="#" role="button">
                        {{ __('Quantity') }}
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
                        <img style="width: 50px;"
                             src="{{ $product->product_image ? asset('storage/' . $product->product_image) : asset('assets/img/products/default.webp') }}"
                             alt="">
                    </td>
                    <td class="align-middle text-center">
                        {{ $product->name }}
                    </td>
                    <td class="align-middle text-center">
                        {{ $product->quantity }}
                    </td>
                    <td class="align-middle text-center" style="width: 10%">
                        <div class="d-flex">

                            @php
                                $url = request()->url();
                                $orderId = basename($url);
                                $isUpdateOrderUrl = !str_contains($url, 'create');
                            @endphp

                            <button
                                wire:click="{{ $isUpdateOrderUrl ? "updateOrderDetails({$product->id}, {$product->selling_price})" : "addCartItem({$product->id}, '{$product->name}', {$product->selling_price})" }}"
                                class="btn btn-icon btn-outline-primary" style="width: 20px">
                                <x-icon.plus/>
                            </button>
                        </div>
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
        <ul class="pagination m-0 ms-auto">
            {{ $products->links() }}
        </ul>
    </div>
</div>
