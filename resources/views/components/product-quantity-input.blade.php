<div>
    <div class="col-sm-6 col-md-6">
        <div class="mb-3">
            <label for="quantity" class="form-label">
                {{ __('Quantity') }}
                <span class="text-danger">*</span>
                <div id="stock-message"></div>
            </label>

            <div class="input-group">
                <input type="text"
                       id="quantity"
                       name="quantity"
                       class="form-control bg-lighter @error('quantity') is-invalid @enderror"
                       min="0"
                       value="{{ old('quantity', $product->quantity) }}"
                       placeholder="0"
                       style="background-color: #f8fafc; color: #1e293b; cursor: not-allowed;"
                       readonly>

                <button type="button"
                        class="btn btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#refillModal">
                    {{ __('Refill Stock') }}
                </button>

                @error('quantity')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
        </div>

        @livewire('product-refill', ['product' => $product])
    </div>
</div>
