<table class="table table-striped table-bordered align-middle">
    <thead class="thead-light">
    <tr>
        <th scope="col">
            {{ __('Product') }}
        </th>
        <th scope="col" class="text-center">{{ __('Quantity') }}</th>
        <th scope="col" class="text-center">{{ __('Price') }}</th>
        <th scope="col" class="text-center">{{ __('SubTotal') }}</th>
        <th scope="col" class="text-center">
            {{ __('Action') }}
        </th>
    </tr>
    </thead>
    <tbody>
    @forelse ($order_details as $item)
        <tr>
            <td>
                {{ $item->product->name }}
            </td>
            <td style="min-width: 170px;">
                <div class="input-group">
                    <input type="number" class="form-control" name="qty" wire:change="updateQuantity('{{ $item->product->id }}', $event.target.value)" value="{{ old('qty', $item->quantity) }}" min="1" required />
                </div>
            </td>
            <td class="text-center">
                {{ $item->unitcost }}
            </td>
            <td class="text-center">
                {{ $item->total }}
            </td>
            <td class="text-center">
                <button wire:click="delete('{{ $item->id }}')" class="btn btn-icon btn-outline-danger">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-trash" width="24"
                         height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M4 7l16 0"/>
                        <path d="M10 11l0 6"/>
                        <path d="M14 11l0 6"/>
                        <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/>
                        <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/>
                    </svg>
                </button>
            </td>
        </tr>
    @empty
        <td colspan="5" class="text-center">
            {{ __('Add Products') }}
        </td>
    @endforelse
    <tr>
        <td colspan="4" class="text-end">
            Total Product
        </td>
        <td class="text-center">
            {{ $order->total_products }}
        </td>
    </tr>
    <tr>
        <td colspan="4" class="text-end">Subtotal</td>
        <td class="text-center">
            {{ $order->sub_total }}
        </td>
    </tr>
    <tr>
        <td colspan="4" class="text-end">Tax</td>
        <td class="text-center">
            {{ $order->vat }}
        </td>
    </tr>
    <tr>
        <td colspan="4" class="text-end">Total</td>
        <td class="text-center">
            {{ $order->total }}
        </td>
    </tr>
    </tbody>
</table>
