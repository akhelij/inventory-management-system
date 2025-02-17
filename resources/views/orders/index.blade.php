@extends('layouts.tabler')

@section('content')
    <div class="page-body">
        @if (!$orders)
            <x-empty title="No orders found" message="Try adjusting your search or filter to find what you're looking for."
                button_label="{{ __('Add your first Order') }}" button_route="{{ route('orders.create') }}" />
        @else
            <div class="container-xl">
                <livewire:tables.order-table />
            </div>

            <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050">
                <div id="successToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header bg-success text-white">
                        <strong class="me-auto">Success</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body"></div>
                </div>

                <div id="errorToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header bg-danger text-white">
                        <strong class="me-auto">Error</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body"></div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('page-scripts')
    <script>
        // Initialize modal and toasts
        let warningModal;

        document.addEventListener('DOMContentLoaded', function() {
            warningModal = new bootstrap.Modal(document.getElementById('warningModal'));
        });

        Livewire.on('showWarningModal', () => {
            warningModal.show();
        });

        Livewire.on('hideWarningModal', () => {
            warningModal.hide();
        });
        document.addEventListener('livewire:initialized', () => {
            const successToast = new bootstrap.Toast(document.getElementById('successToast'));
            const errorToast = document.getElementById('errorToast');

            Livewire.on('orderStatusUpdated', (event) => {
                document.querySelector('#successToast .toast-body').textContent = event.message;
                successToast.show();
            });

            Livewire.on('orderStatusError', (event) => {
                document.querySelector('#errorToast .toast-body').textContent = event.message;
                errorToast.show();
            });
        });
    </script>
@endpush
