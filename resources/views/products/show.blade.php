@extends('layouts.tabler')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center mb-3">
                <div class="col">
                    <h2 class="page-title">
                        {{ $product->name }}
                    </h2>
                </div>

                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="{{ route('products.edit', $product->uuid) }}"
                            class="btn btn-warning d-none d-sm-inline-block">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-pencil"
                                width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" />
                                <path d="M13.5 6.5l4 4" />
                            </svg>
                            {{ __('Edit') }}
                        </a>
                    </div>
                </div>
            </div>

            @include('partials._breadcrumbs')
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <h3 class="card-title">
                                    {{ __('Product Image') }}
                                </h3>

                                <img style="width: 90px;" id="image-preview"
                                    src="{{ $product->product_image ? asset('storage/' . $product->product_image) : asset('assets/img/products/default.webp') }}"
                                    alt="" class="img-account-profile mb-2">
                            </div>
                            <div class="table-responsive">
                                <h3 class="card-title ms-3">
                                    {{ __('Stock history') }}
                                </h3>
                                <table class="table table-bordered card-table table-vcenter text-nowrap datatable">
                                    <thead>
                                    <tr>
                                        <th>{{ __('Quantity') }}</th>
                                        <th>{{ __('Date') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($product_entries as $entry)
                                        <tr>
                                            <td>{{ $entry->quantity_added }}</td>
                                            <td>{{ $entry->created_at }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    {{ __('Product Details') }}
                                </h3>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered card-table table-vcenter text-nowrap datatable">
                                    <tbody>
                                        <tr>
                                            <td>{{ __('Name') }}</td>
                                            <td>{{ $product->name }}</td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Slug') }}</td>
                                            <td>{{ $product->slug }}</td>
                                        </tr>
                                        <tr>
                                            <td><span class="text-secondary">{{ __('Code') }}</span></td>
                                            <td>{{ $product->code }}</td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Barcode') }}</td>
                                            <td>{!! $barcode !!}</td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Category') }}</td>
                                            <td>
                                                <a href="{{ route('categories.show', $product->category) }}" class="badge bg-blue-lt">
                                                    {{ $product->category->name }}
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Unit') }}</td>
                                            <td>
                                                <a href="{{ route('categories.show', $product->unit) }}" class="badge bg-blue-lt">
                                                    {{ $product->unit->short_code }}
                                                </a>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>{{ __('Quantity') }}</td>
                                            <td>{{ $product->quantity }}</td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Quantity Alert') }}</td>
                                            <td>
                                                <span class="badge bg-red-lt">
                                                    {{ $product->quantity_alert }}
                                                </span>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>{{ __('Buying Price') }}</td>
                                            <td>{{ $product->buying_price }}</td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Selling Price') }}</td>
                                            <td>{{ $product->selling_price }}</td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Tax') }}</td>
                                            <td>
                                                <span class="badge bg-red-lt">
                                                    {{ $product->tax }} %
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Tax Type') }}</td>
                                            <td>{{ $product->tax_type->label() }}</td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Notes') }}</td>
                                            <td>{{ $product->notes }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="card-footer text-end">
                                <a class="btn btn-info" href="{{ url()->previous() }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrow-left"
                                        width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M5 12l14 0" />
                                        <path d="M5 12l6 6" />
                                        <path d="M5 12l6 -6" />
                                    </svg>
                                    {{ __('Back') }}
                                </a>
                                <a class="btn btn-warning" href="{{ route('products.edit', $product->uuid) }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-pencil"
                                        width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" />
                                        <path d="M13.5 6.5l4 4" />
                                    </svg>
                                    {{ __('Edit') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
