/**
 * Toast notification system
 */
class ToastManager {
    constructor() {
        this.initContainer();
    }

    initContainer() {
        // Check if we have an existing toast container
        let toastContainer = document.getElementById('toast-container');
        
        // Create one if it doesn't exist
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = "1050";
            document.body.appendChild(toastContainer);
        }
        
        // Add styles if needed
        if (!document.getElementById('toast-fallback-styles')) {
            const styleEl = document.createElement('style');
            styleEl.id = 'toast-fallback-styles';
            styleEl.textContent = `
                .toast {
                    position: relative;
                    min-width: 300px;
                    margin-bottom: 10px;
                    background-color: white;
                    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
                    border-radius: 0.25rem;
                    overflow: hidden;
                }
                .toast-header {
                    display: flex;
                    align-items: center;
                    padding: 0.5rem;
                    color: white;
                    background-color: #17a2b8;
                }
                .toast-header .me-auto {
                    margin-right: auto;
                }
                .toast-body {
                    padding: 0.75rem;
                }
                .btn-close {
                    cursor: pointer;
                    background: transparent;
                    border: 0;
                    font-size: 1.5rem;
                    font-weight: 700;
                    line-height: 1;
                    color: white;
                    opacity: 0.5;
                }
                .btn-close:hover {
                    opacity: 1;
                }
            `;
            document.head.appendChild(styleEl);
        }
    }

    show(title, message, type = 'info') {
        const toastContainer = document.getElementById('toast-container');
        
        // Generate a unique ID for this toast
        const toastId = 'toast-' + Date.now();
        
        // Determine header background color based on type
        let bgClass = 'bg-info';
        if (type === 'success') bgClass = 'bg-success';
        if (type === 'error') bgClass = 'bg-danger';
        if (type === 'warning') bgClass = 'bg-warning';
        
        // Create toast HTML
        const toastHtml = `
            <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header ${bgClass} text-white">
                    <strong class="me-auto">${title}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;
        
        // Add toast to the container
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        
        // Initialize and show the toast
        const toastEl = document.getElementById(toastId);
        
        // Check if Bootstrap is available
        if (typeof bootstrap !== 'undefined') {
            const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
            toast.show();
            
            // Remove the toast after it's hidden
            toastEl.addEventListener('hidden.bs.toast', function () {
                toastEl.remove();
            });
        } else {
            // Fallback for when Bootstrap JS is not available
            toastEl.style.display = 'block';
            toastEl.style.opacity = '1';
            
            // Manually add close functionality
            const closeBtn = toastEl.querySelector('.btn-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    toastEl.remove();
                });
            }
            
            // Auto-remove after 3 seconds
            setTimeout(() => {
                if (toastEl.parentNode) {
                    toastEl.remove();
                }
            }, 3000);
        }
    }
}

// Create a global instance of ToastManager
window.toastManager = new ToastManager();

// Helper functions
window.showToast = function(title, message, type = 'info') {
    window.toastManager.show(title, message, type);
};

window.showSuccessToast = function(message) {
    window.toastManager.show('Success', message, 'success');
};

window.showErrorToast = function(message) {
    window.toastManager.show('Error', message, 'error');
};

window.showInfoToast = function(message) {
    window.toastManager.show('Info', message, 'info');
};

window.showWarningToast = function(message) {
    window.toastManager.show('Warning', message, 'warning');
}; 