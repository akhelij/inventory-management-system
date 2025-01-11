<div>
    <div class="modal fade" id="refillModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Refill Stock') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if (session()->has('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <h3 class="mb-3">{{ __('Current Stock') }}: {{ $product->quantity }}</h3>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Refill Quantity') }}</label>
                        <input type="number" class="form-control @error('refillQuantity') is-invalid @enderror"
                               wire:model.defer="refillQuantity"
                               min="1">
                        @error('refillQuantity')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ __('Close') }}
                    </button>
                    <button type="button" class="btn btn-primary" wire:click="refillStock">
                        {{ __('Refill Stock') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
