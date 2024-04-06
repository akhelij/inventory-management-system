@extends('layouts.tabler')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">
                            {{ __('Edit Role') }}
                        </h3>
                    </div>

                    <div class="card-actions">
                        <x-action.close route="{{ route('roles.index') }}"/>
                    </div>
                </div>
                <form action="{{ route('roles.update', $role->id) }}" method="POST">
                    @csrf
                    @method('put')
                    <div class="card-body">
                        <x-input
                            label="{{ __('Name') }}"
                            id="name"
                            name="name"
                            :value="old('name', $role->name)"
                            required
                        />
                        <table>
                            @foreach($permissions as $index => $permission)
                                @if($index % 4 == 0)
                                    <tr>
                                        @endif
                                        <td class="me-4 mt-1 mb-1" width="10%">
                                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                                   @if($role->permissions->contains($permission)) checked @endif>
                                            {{ $permission->name }}
                                        </td>
                                        @if((($index+1) % 4) == 0)
                                    </tr>
                                @endif
                            @endforeach
                        </table>
                    </div>



                    <div class="card-footer text-end">
                        <x-button type="submit">
                            {{ __('Update') }}
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
