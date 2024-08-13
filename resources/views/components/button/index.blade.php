@props([
    'type' => 'button',
    'target' => '_self',
    'route'
])

@isset($route)
    <a href="{{ $route }}" {{ $attributes->class(['btn btn-primary']) }} target="{{ $target }}">
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class(['btn btn-primary']) }}>
        {{ $slot }}
    </button>
@endisset
