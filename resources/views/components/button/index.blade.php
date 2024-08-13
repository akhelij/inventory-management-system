@props([
    'type' => null ?? 'button',
    'route'
])

@isset($route)
    <a href="{{ $route }}" {{ $attributes->class(['btn btn-primary']) }} target="_blank">
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class(['btn btn-primary']) }}>
        {{ $slot }}
    </button>
@endisset
