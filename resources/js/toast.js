/**
 * Toast notification service for Alami Gestion
 * 
 * Usage:
 * - Call global functions:
 *   showToast('Your message', 'success')
 *   showSuccessToast('Operation completed successfully')
 * 
 * Types: 'info', 'success', 'warning', 'error'
 */

// Create global toast functions
window.showToast = function(message, type = 'info') {
    if (!message) return;
    
    window.dispatchEvent(new CustomEvent('toast', {
        detail: {
            message,
            type
        }
    }));
};

// Shorthand methods
window.showInfoToast = message => showToast(message, 'info');
window.showSuccessToast = message => showToast(message, 'success');
window.showWarningToast = message => showToast(message, 'warning');
window.showErrorToast = message => showToast(message, 'error');

// Livewire integration
document.addEventListener('livewire:initialized', () => {
    // Listen for notify events from Livewire components
    Livewire.on('notify', params => {
        const message = params.message;
        // Map Livewire notification types to our toast types
        let type = 'info';
        if (params.type === 'success') type = 'success';
        if (params.type === 'error' || params.type === 'danger') type = 'error';
        if (params.type === 'warning') type = 'warning';
        
        showToast(message, type);
    });
}); 