// Initialize flatpickr for all elements with datepicker class
document.addEventListener('DOMContentLoaded', function() {
    // Check if flatpickr is available
    if (typeof flatpickr !== 'undefined') {
        flatpickr('.datepicker', {
            dateFormat: 'd-m-Y',
            allowInput: true,
            altInput: true,
            altFormat: 'd-m-Y',
            locale: {
                firstDayOfWeek: 1, // Monday
            }
        });
    } else {
        console.warn('Flatpickr library not loaded. Please make sure it is included in your page.');
    }
});
