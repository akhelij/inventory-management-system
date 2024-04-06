@extends('layouts.tabler')

@section('content')
<div class="page-body">
    @if($roles->isEmpty())
        <x-empty
            title="No units found"
            message="Try adjusting your search or filter to find what you're looking for."
            button_label="{{ __('Add your first Role') }}"
            button_route="{{ route('roles.create') }}"
        />
    @else
        <div class="container-xl">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">
                            {{ __('Roles') }}
                        </h3>
                    </div>

                    <div class="card-actions">
                        <x-action.create route="{{ route('roles.create') }}" />
                    </div>
                </div>

                <x-spinner.loading-spinner/>

                <div class="table-responsive">
                    <table wire:loading.remove class="table table-bordered card-table table-vcenter text-nowrap datatable">
                        <thead class="thead-light">
                        <tr>
                            <th class="align-middle text-center w-1">
                                {{ __('ID') }}
                            </th>
                            <th scope="col" class="align-middle text-center">
                                {{ __('Name') }}
                            </th>
                            <th scope="col" class="align-middle text-center">
                                {{ __('Action') }}
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($roles as $role)
                            <tr>
                                <td class="align-middle text-center" style="width: 10%">
                                    {{ $role->id }}
                                </td>
                                <td class="align-middle text-center">
                                    {{ $role->name }}
                                </td>
                                <td class="align-middle text-center" style="width: 15%">
                                    <x-button.edit class="btn-icon" route="{{ route('roles.edit', $role) }}"/>
                                    <x-button.delete class="btn-icon" route="{{ route('roles.destroy', $role) }}"/>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="align-middle text-center" colspan="7">
                                    {{ __('No results found') }}
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    @endif
</div>
@endsection
