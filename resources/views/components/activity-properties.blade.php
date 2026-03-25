@if(!empty($properties))
<table class="table">
    <thead>
        <tr>
            <th>{{ __('Property') }}</th>
            <th>{{ __('Old Value') }}</th>
            <th>{{ __('New Value') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ((isset($properties['old']) ?  $properties['old'] : []) as $property => $value)
            <tr>
                <td>{{ ucfirst($property) }}</td>
                <td>{{ $value }}</td>
                <td>{{ $properties['attributes'][$property] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endif
