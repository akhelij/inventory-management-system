<div>
    <div class="modal fade" id="statusUpdateModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Update Status') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if (session()->has('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session()->has('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Current Status') }}</label>
                                <input type="text" class="form-control" value="{{ $currentStatusName }}" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('New Status') }}</label>
                                <input type="text" class="form-control" value="{{ $statusName }}" disabled>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">{{ __('Comment') }}</label>
                        <textarea wire:model="statusComment"
                                  class="form-control @error('statusComment') is-invalid @enderror"
                                  rows="3"
                                  placeholder="{{ __('Please provide a reason for this status change...') }}"
                                  required></textarea>
                        @error('statusComment')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ __('Close') }}
                    </button>
                    <button type="button" class="btn btn-primary" wire:click="updateStatus">
                        {{ __('Update Status') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
