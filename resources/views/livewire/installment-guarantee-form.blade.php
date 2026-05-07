<div>
    <div class="modal fade" id="guaranteeModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-shield-alt me-1"></i>
                        @if ($guaranteeId)
                            {{ __('Edit Guarantee') }}
                        @else
                            {{ __('Add Guarantee') }}
                        @endif
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    @if ($error)
                        <div class="alert alert-danger">{{ $error }}</div>
                    @endif

                    <ul class="nav nav-pills mb-3" role="tablist">
                        <li class="nav-item">
                            <button type="button"
                                    class="nav-link {{ $type === 'person' ? 'active' : '' }}"
                                    wire:click="setType('person')">
                                <i class="fas fa-user me-1"></i>{{ __('Person') }}
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button"
                                    class="nav-link {{ $type === 'cheque' ? 'active' : '' }}"
                                    wire:click="setType('cheque')">
                                <i class="fas fa-money-check me-1"></i>{{ __('Cheque') }}
                            </button>
                        </li>
                    </ul>

                    @if ($type === 'person')
                        @if ($personPreview)
                            @if (! empty($personPreview['cin_photo']))
                                <div class="mb-3 text-center">
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($personPreview['cin_photo']) }}"
                                         alt="CIN"
                                         class="img-fluid rounded border"
                                         style="max-height: 220px;">
                                </div>
                            @endif
                            <div class="card mb-3">
                                <div class="card-body d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="fw-bold">{{ $personPreview['name'] }}</div>
                                        <div class="text-muted small">
                                            {{ __('CIN') }}: {{ $personPreview['cin'] ?? '-' }}
                                            · {{ __('Phone') }}: {{ $personPreview['phone'] ?? '-' }}
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="clearPerson">
                                        <i class="fas fa-times me-1"></i>{{ __('Change') }}
                                    </button>
                                </div>
                            </div>
                        @else
                            <div class="mb-3">
                                <label class="form-label">{{ __('Search a customer by name or CIN') }}</label>
                                <input type="text" class="form-control"
                                       wire:model.live.debounce.300ms="customerSearch"
                                       placeholder="{{ __('Type a name or CIN…') }}">

                                @if ($customerMatches->isNotEmpty())
                                    <div class="list-group mt-2" style="max-height: 280px; overflow-y: auto;">
                                        @foreach ($customerMatches as $c)
                                            <button type="button"
                                                    class="list-group-item list-group-item-action d-flex align-items-center gap-2"
                                                    wire:click="selectPerson({{ $c->id }})">
                                                @if ($c->cin_photo)
                                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($c->cin_photo) }}"
                                                         alt=""
                                                         class="rounded"
                                                         style="height: 40px; width: 60px; object-fit: cover; flex-shrink: 0;">
                                                @else
                                                    <div class="rounded bg-light d-flex align-items-center justify-content-center text-muted"
                                                         style="height: 40px; width: 60px; flex-shrink: 0;">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                @endif
                                                <span class="flex-fill text-start">
                                                    <strong class="d-block">{{ $c->name }}</strong>
                                                    <span class="text-muted small">
                                                        {{ $c->cin ?? '—' }} · {{ $c->phone ?? '—' }}
                                                    </span>
                                                </span>
                                                <i class="fas fa-arrow-right text-muted"></i>
                                            </button>
                                        @endforeach
                                    </div>
                                @elseif (filled($customerSearch))
                                    <div class="text-muted small mt-2">{{ __('No matching customer.') }}</div>
                                @endif
                            </div>
                        @endif

                        @error('personCustomerId')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    @else
                        @if ($chequePhoto)
                            <div class="mb-3 text-center">
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($chequePhoto) }}"
                                     alt="Cheque"
                                     class="img-fluid rounded border"
                                     style="max-height: 220px;">
                            </div>
                        @endif

                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="mb-2">
                                    <i class="fas fa-camera me-1"></i>{{ $chequePhoto ? __('Replace cheque image (optional)') : __('Scan cheque (optional)') }}
                                </h6>
                                <livewire:cheque-scanner :key="'guarantee-scanner-'.($scheduleId ?? 0).'-'.($guaranteeId ?? 'new')" />
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label required">{{ __('Amount') }} (MAD)</label>
                                <input type="number" step="0.01" class="form-control" wire:model="chequeAmount">
                                @error('chequeAmount') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Cheque number') }}</label>
                                <input type="text" class="form-control" wire:model="chequeNature">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Bank') }}</label>
                                <input type="text" class="form-control" wire:model="chequeBank">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Echeance') }}</label>
                                <input type="date" class="form-control" wire:model="chequeEcheance">
                            </div>
                        </div>
                    @endif
                </div>

                <div class="modal-footer">
                    @if ($guaranteeId)
                        <button type="button" class="btn btn-outline-danger me-auto"
                                wire:click="delete"
                                wire:confirm="{{ __('Remove this guarantee?') }}">
                            <i class="fas fa-trash me-1"></i>{{ __('Delete') }}
                        </button>
                    @endif
                    <button type="button" class="btn" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-primary" wire:click="save"
                            wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">
                            <i class="fas fa-check me-1"></i>{{ __('Save') }}
                        </span>
                        <span wire:loading wire:target="save">{{ __('Saving…') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
