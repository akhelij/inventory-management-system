<div>
    <div class="modal fade" id="transferModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Transfer Stock') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <div class="alert alert-info mb-0 py-2">
                                <strong>{{ __('Source') }}:</strong>
                                {{ $product->name }} ({{ $product->code }})
                                — {{ __('Warehouse') }}: {{ $product->warehouse?->name ?? '-' }}
                                — {{ __('Available') }}: {{ $product->quantity }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">{{ __('Quantity to transfer') }}</label>
                            <input type="number" class="form-control @error('transferQuantity') is-invalid @enderror"
                                   wire:model.live="transferQuantity"
                                   min="1" max="{{ $product->quantity }}">
                            <div class="form-hint">{{ __('Max') }}: {{ $product->quantity }}</div>
                            @error('transferQuantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">{{ __('Destination warehouse') }}</label>
                            <select class="form-select @error('destinationWarehouseId') is-invalid @enderror"
                                    wire:model.live="destinationWarehouseId">
                                <option value="">{{ __('Select a warehouse') }}</option>
                                @foreach ($warehouses as $w)
                                    <option value="{{ $w->id }}">{{ $w->name }}</option>
                                @endforeach
                            </select>
                            @error('destinationWarehouseId')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if ($destinationWarehouseId)
                            <div class="col-md-12">
                                <label class="form-label required">{{ __('Destination product') }}</label>
                                <input type="text" class="form-control mb-2"
                                       placeholder="{{ __('Search by reference (code) or name…') }}"
                                       wire:model.live.debounce.300ms="productSearch">
                                <div class="form-hint mb-2">
                                    {{ __('You can find the product by reference. The closest match is suggested by default.') }}
                                </div>

                                @if ($productMatches->isEmpty())
                                    <div class="text-muted small">{{ __('No matching products in this warehouse.') }}</div>
                                @else
                                    <div class="list-group" style="max-height: 240px; overflow-y: auto;">
                                        @foreach ($productMatches as $match)
                                            <label class="list-group-item d-flex align-items-center {{ $destinationProductId === $match->id ? 'active' : '' }}">
                                                <input class="form-check-input me-2" type="radio"
                                                       name="destinationProductId"
                                                       value="{{ $match->id }}"
                                                       wire:model.live="destinationProductId">
                                                <span class="flex-fill">
                                                    <strong>{{ $match->code }}</strong>
                                                    — {{ $match->name }}
                                                </span>
                                                <span class="badge bg-secondary-lt">{{ __('Stock') }}: {{ $match->quantity }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @endif
                                @error('destinationProductId')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                    <button type="button" class="btn btn-primary" wire:click="transfer"
                            wire:loading.attr="disabled" wire:target="transfer">
                        <span wire:loading.remove wire:target="transfer">
                            <i class="fas fa-exchange-alt me-1"></i>{{ __('Transfer') }}
                        </span>
                        <span wire:loading wire:target="transfer">{{ __('Transferring…') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
