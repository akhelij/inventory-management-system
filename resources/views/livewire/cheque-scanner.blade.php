<div x-data="{
    handlePaste(e) {
        const items = e.clipboardData?.items;
        if (!items) return;
        for (const item of items) {
            if (item.type.startsWith('image/')) {
                const file = item.getAsFile();
                const dt = new DataTransfer();
                dt.items.add(file);
                $refs.chequeInput.files = dt.files;
                $refs.chequeInput.dispatchEvent(new Event('change', { bubbles: true }));
                break;
            }
        }
    }
 }"
 x-on:paste.window="handlePaste($event)">

    <div class="d-flex align-items-center gap-3 mb-3">
        <div class="flex-grow-1">
            <input type="file" wire:model="chequeImage" accept="image/*" capture="environment"
                x-ref="chequeInput"
                class="form-control @error('chequeImage') is-invalid @enderror">
            @error('chequeImage')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        @if ($chequeImage)
            <button wire:click="scan" wire:loading.attr="disabled" class="btn btn-primary flex-shrink-0">
                <span wire:loading.remove wire:target="scan">
                    <i class="fas fa-search me-1"></i>{{ __('Scan') }}
                </span>
                <span wire:loading wire:target="scan">
                    <span class="spinner-border spinner-border-sm me-1"></span>{{ __('Processing...') }}
                </span>
            </button>
        @endif
    </div>

    <p class="text-muted small mb-3">
        <i class="fas fa-info-circle me-1"></i>{{ __('Upload a cheque photo or paste from clipboard (Ctrl+V) then click Scan to auto-fill the form.') }}
    </p>

    @if ($chequeImage)
        <div class="mb-3">
            <img src="{{ $chequeImage->temporaryUrl() }}" alt="Cheque Preview"
                class="img-fluid rounded" style="max-height: 160px;">
        </div>
    @endif

    @if ($error)
        <div class="alert alert-warning mb-0">
            <i class="fas fa-exclamation-triangle me-1"></i>{{ $error }}
        </div>
    @elseif ($scanned)
        <div class="alert alert-success mb-0">
            <i class="fas fa-check me-1"></i>{{ __('Cheque data extracted! Fields have been filled.') }}
        </div>
    @endif
</div>
