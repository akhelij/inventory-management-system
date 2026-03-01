@pushonce('page-styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
@endpushonce

@props([
    'label' => '',
    'name',
    'id' => null,
    'placeholder' => 'Search...',
    'data',
    'value' => null,
    'required' => false,
])

@php $selectId = $id ?? $name; @endphp

@if ($label)
    <label for="{{ $selectId }}" class="form-label @if($required) required @endif">
        {{ $label }}
    </label>
@endif

<select id="{{ $selectId }}" name="{{ $name }}" placeholder="{{ $placeholder }}" autocomplete="off"
        class="form-select @error($name) is-invalid @enderror" @if($required) required @endif>
    <option value="">{{ $placeholder }}</option>

    @foreach($data as $option)
        <option value="{{ $option->id }}" @selected(old($name, $value) == $option->id)>
            {{ $option->name }}
        </option>
    @endforeach
</select>

@error($name)
    <div class="invalid-feedback">
        {{ $message }}
    </div>
@enderror

@pushonce('page-scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
@endpushonce

@push('page-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new TomSelect("#{{ $selectId }}", {
                sortField: { field: "text", direction: "asc" },
                allowEmptyOption: true,
            });
        });
    </script>
@endpush
