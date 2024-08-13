@props([
    'target' => '_self',
    'route'
])

<x-button {{ $attributes->class(['btn btn-outline-info']) }} route="{{ $route }}" target="{{ $target }}">
    <x-icon.eye/>
    {{ $slot }}
</x-button>
