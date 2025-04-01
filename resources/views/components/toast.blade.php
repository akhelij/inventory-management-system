@props([
    'type' => 'info',
    'title' => null,
    'position' => 'top-end',
    'timeout' => 5000
])

<style>
    .toast-container {
        position: fixed !important;
        pointer-events: none;
        z-index: 9999;
    }
    
    .toast-content {
        pointer-events: auto;
        min-width: 250px;
        max-width: 350px;
        border-radius: 0.375rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        backdrop-filter: blur(5px);
        transition: all 0.2s ease;
        margin-bottom: 0.75rem;
    }
    
    .toast-content:last-child {
        margin-bottom: 0;
    }
    
    .toast-close {
        background: transparent;
        border: 0;
        opacity: 0.7;
        transition: opacity 0.2s;
    }
    
    .toast-close:hover {
        opacity: 1;
    }
    
    /* Custom positions */
    .toast-top-right {
        top: 1rem;
        right: 1rem;
    }
    
    .toast-top-left {
        top: 1rem;
        left: 1rem;
    }
    
    .toast-bottom-right {
        bottom: 1rem;
        right: 1rem;
    }
    
    .toast-bottom-left {
        bottom: 1rem;
        left: 1rem;
    }
    
    .toast-top-center {
        top: 1rem;
        left: 50%;
        transform: translateX(-50%);
    }
    
    .toast-bottom-center {
        bottom: 1rem;
        left: 50%;
        transform: translateX(-50%);
    }
</style>

@php
    $positionMap = [
        'top-end' => 'toast-top-right',
        'top-start' => 'toast-top-left',
        'bottom-end' => 'toast-bottom-right',
        'bottom-start' => 'toast-bottom-left',
        'top-center' => 'toast-top-center',
        'bottom-center' => 'toast-bottom-center',
    ];
    
    $positionClass = $positionMap[$position] ?? $positionMap['top-end'];
@endphp

<div
    x-data="{ show: false, message: '', type: '{{ $type }}', position: '{{ $position }}' }"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform translate-y-2"
    x-transition:enter-end="opacity-100 transform translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 transform translate-y-0"
    x-transition:leave-end="opacity-0 transform translate-y-2"
    @toast.window="
        show = true;
        message = $event.detail.message;
        type = $event.detail.type || '{{ $type }}';
        setTimeout(() => { show = false }, {{ $timeout }});
    "
    class="toast-container {{ $positionClass }}"
    style="display: none;"
    role="alert"
>
    <div 
        :class="{
            'bg-blue text-white': type === 'info',
            'bg-green text-white': type === 'success',
            'bg-yellow text-dark': type === 'warning',
            'bg-red text-white': type === 'error'
        }" 
        class="toast-content d-flex align-items-center p-3"
    >
        <div class="me-3" x-show="type === 'info'">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                <path d="M12 9h.01"></path>
                <path d="M11 12h1v4h1"></path>
                <path d="M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z"></path>
            </svg>
        </div>
        <div class="me-3" x-show="type === 'success'">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                <path d="M5 12l5 5l10 -10"></path>
            </svg>
        </div>
        <div class="me-3" x-show="type === 'warning'">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                <path d="M12 9v2m0 4v.01"></path>
                <path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"></path>
            </svg>
        </div>
        <div class="me-3" x-show="type === 'error'">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                <path d="M18.364 18.364a9 9 0 1 1 -12.728 -12.728a9 9 0 0 1 12.728 12.728z"></path>
                <path d="M12 12l.01 0"></path>
                <path d="M12 8l0 4"></path>
            </svg>
        </div>
        <div class="flex-grow-1 text-break" x-text="message"></div>
        <button 
            type="button" 
            @click="show = false" 
            class="toast-close ms-2 rounded p-1"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                <path d="M18 6l-12 12"></path>
                <path d="M6 6l12 12"></path>
            </svg>
        </button>
    </div>
</div> 