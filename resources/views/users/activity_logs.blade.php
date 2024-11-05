@extends('layouts.tabler')

@section('content')
<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">
                        {{ __('Activities') }}
                    </h3>
                </div>
            </div>

            <x-spinner.loading-spinner/>

            <div class="table-responsive">
                <table class="table table-bordered card-table table-vcenter text-nowrap datatable">
                    <thead class="thead-light">
                    <tr>
                        <th scope="col" class="align-middle text-center">
                            {{ __('Date') }}
                        </th>
                        <th scope="col" class="align-middle text-center">
                            {{ __('Author') }}
                        </th>
                        <th scope="col" class="align-middle text-center">
                            {{ __('Subject') }}
                        </th>
                        <th scope="col" class="align-middle text-center">
                            {{ __('Changes') }}
                        </th>
                        <th class="align-middle text-center w-1">
                            {{ __('Event') }}
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($activities as $activity)
                        <tr>
                            <td class="align-middle text-center d-none d-sm-table-cell" style="width: 15%">
                                {{ $activity->created_at->format('d-m-Y') }}
                            </td>
                            <td class="align-middle text-center d-none d-sm-table-cell">
                                {{ $activity->causer?->name }}
                            </td>
                            <td class="align-middle text-center">
                                @if($activity->event == 'deleted')
                                    Subject deleted
                                @else
                                    <a href="{{ route(Str::plural(lcfirst(str_replace('App\\Models\\', '', $activity->subject_type))) . '.show', $activity->subject_id) }}">
                                        {{ $activity->subject_type }}
                                    </a>
                                @endif
                            </td>
                            <td class="align-middle text-center">
                                @if($activity->event !== 'deleted')
                                    <x-activity-properties :properties="$activity->changes?->toArray()" />
                                @endif
                            </td>
                            <td class="align-middle text-center" style="width: 10%">
                                {{ $activity->event }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="align-middle text-center" colspan="7">
                                {{ __('products.no_results_found') }}
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer d-flex align-items-center">
               <ul class="pagination m-0 ms-auto">
                    {{ $activities->links() }}
                </ul>
            </div>
        </div>

    </div>
</div>
@endsection
