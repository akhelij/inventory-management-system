@extends('layouts.tabler')

@section('content')
    <div class="page-body">
        @if (!$drivers)
            <x-empty title="{{ __('No drivers found') }}"
                     message="{{ __('Try adjusting your search or filter to find what you\'re looking for.') }}"
                     button_label="{{ __('Add your first driver') }}" button_route="{{ route('drivers.create') }}" />
        @else
            <div class="container-xl">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h3 class="card-title">
                                {{ __('Drivers') }}
                            </h3>
                        </div>

                        <div class="card-actions">
                            <x-action.create route="{{ route('drivers.create') }}" />
                        </div>
                    </div>

                    <div class="card-body border-bottom py-3">
                    </div>

                    <x-spinner.loading-spinner />

                    <div class="table-responsive">
                        <table class="table table-bordered card-table table-vcenter text-nowrap datatable">
                            <thead class="thead-light">
                            <tr>
                                <th class="align-middle text-center w-1">
                                    {{ __('ID') }}
                                </th>
                                <th scope="col" class="align-middle text-center">
                                    {{ __('Name') }}
                                </th>
                                <th scope="col" class="align-middle text-center">
                                    {{ __('Phone') }}
                                </th>
                                <th scope="col" class="align-middle text-center">
                                    {{ __('License Number') }}
                                </th>
                                <th scope="col" class="align-middle text-center d-none d-sm-table-cell">
                                    {{ __('Created at') }}
                                </th>
                                <th scope="col" class="align-middle text-center">
                                    {{ __('Action') }}
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($drivers as $driver)
                                <tr>
                                    <td class="align-middle text-center" style="width: 10%">
                                        {{ $driver->id }}
                                    </td>
                                    <td class="align-middle text-center">
                                        {{ $driver->name }}
                                    </td>
                                    <td class="align-middle text-center">
                                        {{ $driver->phone }}
                                    </td>
                                    <td class="align-middle text-center">
                                        {{ $driver->license_number }}
                                    </td>
                                    <td class="align-middle text-center d-none d-sm-table-cell" style="width: 15%">
                                        {{ $driver->created_at ? $driver->created_at->format('d-m-Y') : '--' }}
                                    </td>
                                    <td class="align-middle text-center" style="width: 15%">
                                        <x-button.edit class="btn-icon" route="{{ route('drivers.edit', $driver) }}" />
                                        <x-button.delete class="btn-icon" route="{{ route('drivers.destroy', $driver) }}"
                                                         onclick="return confirm('are you sure!')" />
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="align-middle text-center" colspan="6">
                                        {{ __('No results found') }}
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer d-flex align-items-center">
                        <ul class="pagination m-0 ms-auto">
                            {{ $drivers->links() }}
                        </ul>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
