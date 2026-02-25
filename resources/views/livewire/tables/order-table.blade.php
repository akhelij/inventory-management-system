<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">
                {{ __('Orders') }}
            </h3>
        </div>

        <div class="card-actions">
            @if(auth()->user()->hasRole('admin'))
                <button onclick="recalculateAllTotals()" class="btn btn-warning me-2" title="Recalculate All Order Totals">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-calculator" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <rect x="4" y="3" width="16" height="18" rx="2" />
                        <rect x="8" y="7" width="8" height="3" />
                        <line x1="8" y1="14" x2="8" y2="14.01" />
                        <line x1="12" y1="14" x2="12" y2="14.01" />
                        <line x1="16" y1="14" x2="16" y2="14.01" />
                        <line x1="8" y1="17" x2="8" y2="17.01" />
                        <line x1="12" y1="17" x2="12" y2="17.01" />
                        <line x1="16" y1="17" x2="16" y2="17.01" />
                    </svg>
                    Fix Totals
                </button>
            @endif
            <form action="{{ route('order.bulk.download', ['order_ids' => $order_ids]) }}" method="POST" enctype="multipart/form-data" class="d-inline">
                  @csrf
                <x-action.create route="{{ route('orders.create') }}"/>
                <button type="submit" class="btn btn-primary">
                    <x-icon.printer/>
                </button>
            </form>
        </div>
    </div>
    <div class="card-body border-bottom py-3">
        <div class="d-flex">
            <div class="text-secondary">
                {{__('Show')}}
                <div class="mx-2 d-inline-block">
                    <select wire:model.live="perPage" class="form-select form-select-sm" aria-label="result per page">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="25">25</option>
                    </select>
                </div>
                {{__('entries')}}
            </div>
            <div class="ms-auto text-secondary">
                {{__('Search')}}:
                <div class="ms-2 d-inline-block">
                    <input type="text" wire:model.live="search" class="form-control form-control-sm"
                           aria-label="Search invoice">
                </div>
            </div>
            <div class="ms-auto text-secondary">
                {{__('Filter by Date')}}:
                <div class="ms-2 d-inline-block">
                    <input type="date" wire:model.live="startDate" class="form-control form-control-sm"
                           aria-label="Start date">
                </div>
                <div class="ms-2 d-inline-block">
                    <input type="date" wire:model.live="endDate" class="form-control form-control-sm"
                           aria-label="End date">
                </div>
            </div>
        </div>
    </div>
    <x-spinner.loading-spinner/>

    <div class="table-responsive">
        <table wire:loading.remove class="table table-bordered card-table table-vcenter text-nowrap datatable">
            <thead class="thead-light">
            <tr>
                <th class="align-middle text-center w-1">

                </th>
                <th scope="col" class="align-middle text-center">
                    <a wire:click.prevent="sortBy('invoice_no')" href="#" role="button">
                        {{ __('Invoice No.') }}
                        @include('inclues._sort-icon', ['field' => 'invoice_no'])
                    </a>
                </th>
                <th scope="col" class="align-middle text-center">
                    <a wire:click.prevent="sortBy('user_id')" href="#" role="button">
                        {{ __('Author') }}
                        @include('inclues._sort-icon', ['field' => 'user_id'])
                    </a>
                </th>
                <th scope="col" class="align-middle text-center">
                    <a wire:click.prevent="sortBy('customer_id')" href="#" role="button">
                        {{ __('Customer') }}
                        @include('inclues._sort-icon', ['field' => 'customer_id'])
                    </a>
                </th>
                <th scope="col" class="align-middle text-center">
                    <a wire:click.prevent="sortBy('order_date')" href="#" role="button">
                        {{ __('Date') }}
                        @include('inclues._sort-icon', ['field' => 'order_date'])
                    </a>
                </th>
                <th scope="col" class="align-middle text-center">
                    <a wire:click.prevent="sortBy('payment_type')" href="#" role="button">
                        {{ __('Payment') }}
                        @include('inclues._sort-icon', ['field' => 'payment_type'])
                    </a>
                </th>
                <th scope="col" class="align-middle text-center">
                    <a wire:click.prevent="sortBy('total')" href="#" role="button">
                        {{ __('Total') }}
                        @include('inclues._sort-icon', ['field' => 'total'])
                    </a>
                </th>
                <th scope="col" class="align-middle text-center">
                    <a wire:click.prevent="sortBy('order_status')" href="#" role="button">
                        {{ __('Status') }}
                        @include('inclues._sort-icon', ['field' => 'order_status'])
                    </a>
                </th>
                <th scope="col" class="align-middle text-center">
                    {{ __('Action') }}
                </th>
            </tr>
            </thead>
            <tbody>
            @forelse ($orders as $order)
                <tr>
                    <td class="align-middle text-center">
                        <input type="checkbox" wire:click="selectOrder({{ $order->id }})" wire:ignore>
                    </td>
                    <td class="align-middle text-center">
                        {{ $order->invoice_no }}
                    </td>
                    <td class="align-middle text-center">
                        {{ $order->user->name }}
                        <br/>
                        @if($order->tagged_user_id)
                            <span class="badge bg-primary ms-2 text-white">{{ $order->taggedUser->name }}</span>
                        @endif
                    </td>
                    <td class="align-middle text-center">
                        {{ $order->customer->name }}
                    </td>
                    <td class="align-middle text-center">
                        {{ $order->order_date->format('d-m-Y') }}
                    </td>
                    <td class="align-middle text-center">
                        {{ $order->payment_type }}
                    </td>
                    <td class="align-middle text-center">
                        {{ Number::currency($order->total, 'MAD') }}
                    </td>
                    <td class="align-middle text-center">
                        <div class="d-flex justify-content-center dropdown" x-data="{ open: false }">
                            <a class="nav-link @if($order->is_updatable_status) dropdown-toggle @endif"
                               href="#" @click.prevent="open = !open" role="button"
                               :aria-expanded="open">
                                <x-status dot color="{{ $order->status_color }}" class="text-uppercase">
                                    {{ $order->status }}
                                </x-status>
                            </a>
                            @if($order->is_updatable_status)
                                <div class="dropdown-menu" :class="{ 'show': open }" @click.outside="open = false">
                                    <div class="dropdown-menu-columns">
                                        <div class="dropdown-menu-column ms-2 me-2">
                                            <a href="#" wire:click.prevent="initiateStatusUpdate({{ $order->id }}, 1)" @click="open = false">
                                                <x-status dot color="green" class="text-uppercase btn">
                                                    {{ __('Approve') }}
                                                </x-status>
                                            </a>
                                            <a href="#" wire:click.prevent="initiateStatusUpdate({{ $order->id }}, 0)" @click="open = false">
                                                <x-status dot color="red" class="text-uppercase btn">
                                                    {{ __('Cancel') }}
                                                </x-status>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </td>
                    <td class="align-middle text-center">
                        <x-button.show class="btn-icon" route="{{ route('orders.show', $order->uuid) }}" target="_blank"/>
                        @if($order->order_status !== \App\Enums\OrderStatus::CANCELED)
                            <x-button.print class="btn-icon" route="{{ route('order.downloadInvoice', $order->uuid) }}"  target="_blank"/>
                        @endif
                        <x-button.delete class="btn-icon" route="{{ route('orders.destroy', $order->uuid) }}"  onclick="return confirm('Vous etes sure !')"/>
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="align-middle text-center" colspan="8">
                        {{__('No results found')}}
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer d-flex align-items-center">
        <p class="m-0 text-secondary">
            {{__('Showing')}} <span>{{ $orders->firstItem() }}</span> {{__('to')}}
            <span>{{ $orders->lastItem() }}</span> {{__('of')}}
            <span>{{ $orders->total() }}</span> {{__('entries')}}
        </p>

        <ul class="pagination m-0 ms-auto">
            {{ $orders->links() }}
        </ul>
    </div>

    @if($showWarningModal)
        <div class="modal show" style="display: block;" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">Warning: Customer Over Limit</h5>
                        <button type="button" class="btn-close" wire:click="cancelStatusUpdate"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            This customer is currently over their credit limit. Are you sure you want to approve this order?
                        </p>
                        @if($newStatus == 0)
                            <div class="mb-3">
                                <label for="statusReason" class="form-label">Reason for Cancellation</label>
                                <textarea wire:model="statusReason" id="statusReason" class="form-control" rows="3"></textarea>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cancelStatusUpdate">
                            Cancel
                        </button>
                        <button type="button" class="btn btn-warning" wire:click="forceApprove">
                            Approve Anyway
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif


</div>
