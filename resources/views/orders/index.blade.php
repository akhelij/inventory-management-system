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
            const errorToast = new bootstrap.Toast(document.getElementById('errorToast'));

            Livewire.on('orderStatusUpdated', (event) => {
                document.querySelector('#successToast .toast-body').textContent = event.message;
                successToast.show();
            });

            Livewire.on('orderStatusError', (event) => {
                document.querySelector('#errorToast .toast-body').textContent = event.message;
                errorToast.show();
            });

            // Reinitialize Bootstrap dropdowns after Livewire morphs the DOM
            // (pagination, sorting, filtering replace elements — stale Dropdown
            // instances hold a null _menu reference and must be disposed first)
            Livewire.hook('morph.updated', ({el, component}) => {
                queueMicrotask(() => {
                    el.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(toggle => {
                        const stale = bootstrap.Dropdown.getInstance(toggle);
                        if (stale) stale.dispose();
                        new bootstrap.Dropdown(toggle);
                    });
                });
            });
        });

        // Function to recalculate all order totals
        function recalculateAllTotals() {
            if (!confirm('This will recalculate totals for ALL orders in the system. Continue?')) {
                return;
            }

            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...';

            fetch('{{ route("orders.recalculate-totals") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                const successToast = new bootstrap.Toast(document.getElementById('successToast'));
                const errorToast = new bootstrap.Toast(document.getElementById('errorToast'));
                
                if (data.success) {
                    let message = `${data.message}\nProcessed: ${data.total_orders} orders\nUpdated: ${data.orders_updated} orders`;
                    if (data.details && data.details.length > 0) {
                        message += '\n\nDetails:\n' + data.details.slice(0, 5).join('\n');
                        if (data.details.length > 5) {
                            message += `\n... and ${data.details.length - 5} more`;
                        }
                    }
                    document.querySelector('#successToast .toast-body').textContent = message;
                    successToast.show();
                    
                    // Refresh the page after 2 seconds to show updated data
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    document.querySelector('#errorToast .toast-body').textContent = data.message || 'Error recalculating totals';
                    errorToast.show();
                }
            })
            .catch(error => {
                const errorToast = new bootstrap.Toast(document.getElementById('errorToast'));
                document.querySelector('#errorToast .toast-body').textContent = 'Network error: ' + error.message;
                errorToast.show();
            })
            .finally(() => {
                button.disabled = false;
                button.innerHTML = originalText;
            });
        }
    </script>
@endpush
