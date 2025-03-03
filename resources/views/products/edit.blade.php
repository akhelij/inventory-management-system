@extends('layouts.tabler')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center mb-3">
                <div class="col">
                    <h2 class="page-title">
                        {{ __('Edit Product') }}
                    </h2>
                </div>
            </div>

            @include('partials._breadcrumbs', ['model' => $product])
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">

                <form action="{{ route('products.update', $product->uuid) }}" method="POST"
                      enctype="multipart/form-data">
                    @csrf
                    @method('put')

                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title">
                                        {{ __('Product Image') }}
                                    </h3>

                                    <img class="img-account-profile mb-2"
                                         src="{{ $product->product_image ? asset('storage/' . $product->product_image) : asset('assets/img/products/default.webp') }}"
                                         alt="" id="image-preview">

                                    <div class="small font-italic text-muted mb-2">
                                        {{ __('JPG or PNG no larger than 2 MB') }}
                                    </div>

                                    <input type="file" accept="image/*" id="image" name="product_image"
                                           class="form-control @error('product_image') is-invalid @enderror"
                                           onchange="previewImage();">

                                    @error('product_image')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8">

                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title">
                                        {{ __('Product Details') }}
                                    </h3>

                                    <div class="row row-cards">
                                        <div class="col-12">
                                            <div class="mb-3">
                                                <label for="warehouse_id" class="form-label">
                                                    {{ __('Product warehouse') }}
                                                    <span class="text-danger">*</span>
                                                </label>

                                                <select name="warehouse_id" id="warehouse_id"
                                                        class="form-select @error('warehouse_id') is-invalid @enderror">
                                                    <option selected=""
                                                            disabled="">{{ __('Select a warehouse:') }}</option>
                                                    @foreach ($warehouses as $warehouse)
                                                        <option value="{{ $warehouse->id }}"
                                                                @if (old('warehouse_id', $product->warehouse_id) == $warehouse->id) selected="selected" @endif>
                                                            {{ $warehouse->name }}</option>
                                                    @endforeach
                                                </select>

                                                @error('warehouse_id')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="name" class="form-label">
                                                    {{ __('Name') }}
                                                    <span class="text-danger">*</span>
                                                </label>

                                                <input type="text" id="name" name="name"
                                                       class="form-control @error('name') is-invalid @enderror"
                                                       placeholder="{{ __('Product name') }}"
                                                       value="{{ old('name', $product->name) }}">

                                                @error('name')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="code" class="form-label">
                                                    {{ __('Référence') }}
                                                    <span class="text-danger">*</span>
                                                </label>

                                                <input type="text" id="code" name="code"
                                                       class="form-control @error('code') is-invalid @enderror"
                                                       placeholder="{{ __('Référence') }}"
                                                       value="{{ old('code', $product->code) }}">

                                                @error('name')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-sm-6 col-md-6" hidden>
                                            <div class="mb-3">
                                                <label for="category_id" class="form-label">
                                                    {{ __('Product category') }}
                                                    <span class="text-danger">*</span>
                                                </label>

                                                <select name="category_id" id="category_id"
                                                        class="form-select @error('category_id') is-invalid @enderror">
                                                    <option selected=""
                                                            disabled="">{{ __('Select a category:') }}</option>
                                                    @foreach ($categories as $category)
                                                        <option value="{{ $category->id }}"
                                                                @if (old('category_id', $product->category_id) == $category->id) selected="selected" @endif>
                                                            {{ $category->name }}</option>
                                                    @endforeach
                                                </select>

                                                @error('category_id')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>
                                        </div>


                                        <div class="col-sm-6 col-md-6">
                                            <div>
                                                <div class="mb-3">
                                                    <label for="quantity" class="form-label">
                                                        {{ __('Quantity') }}
                                                        <span class="text-danger">*</span>
                                                        <div id="stock-message"></div>
                                                    </label>

                                                    <div class="input-group">
                                                        <input type="text"
                                                               id="quantity"
                                                               name="quantity"
                                                               class="form-control bg-lighter @error('quantity') is-invalid @enderror"
                                                               min="0"
                                                               value="{{ old('quantity', $product->quantity) }}"
                                                               placeholder="0"
                                                               style="background-color: #f8fafc; color: #1e293b; cursor: not-allowed;"
                                                               readonly>
                                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#refillModal">
                                                            {{ __('Refill Stock') }}
                                                        </button>
                                                    </div>
                                                    @error('quantity')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        @if(auth()->user()->hasRole('admin'))
                                            <div class="col-12">
                                                <div class="mb-3">
                                                    <label class="form-label" for="buying_price">
                                                        {{ __('Buying price') }}
                                                        <span class="text-danger">*</span>
                                                    </label>

                                                    <input type="text" id="buying_price" name="buying_price"
                                                           class="form-control @error('buying_price') is-invalid @enderror"
                                                           placeholder="0"
                                                           value="{{ old('buying_price', $product->buying_price) }}">

                                                    @error('buying_price')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                    @enderror
                                                </div>
                                            </div>
                                        @endif
                                        <div class="col-12">
                                            <div class="mb-3">
                                                <label for="selling_price" class="form-label">
                                                    {{ __('Selling price') }}
                                                    <span class="text-danger">*</span>
                                                </label>

                                                <input type="text" id="selling_price" name="selling_price"
                                                       class="form-control @error('selling_price') is-invalid @enderror"
                                                       placeholder="0"
                                                       value="{{ old('selling_price', $product->selling_price) }}">

                                                @error('selling_price')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-sm-6 col-md-6" hidden>
                                            <input type="text" id="unit_id" name="unit_id" value="{{ old('unit_id', $product->unit_id) }}">
                                            <input type="number" id="tax" name="tax" value="{{ old('tax', $product->tax) }}">
                                            <input type="text" id="tax_type" name="tax_type" value="{{ old('tax_type', $product->tax_type) }}">
                                            <input type="number" id="quantity_alert" name="quantity_alert" value="{{ old('quantity_alert', $product->quantity_alert) }}">
                                        </div>

                                        <div class="col-md-12">
                                            <div class="mb-3 mb-0">
                                                <label for="notes" class="form-label">
                                                    {{ __('Notes') }}
                                                </label>

                                                <textarea name="notes" id="notes" rows="5"
                                                          class="form-control @error('notes') is-invalid @enderror"
                                                          placeholder="{{ __('Product notes') }}">{{ old('notes', $product->notes) }}</textarea>

                                                @error('notes')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer text-end">
                                    <button class="btn btn-primary" type="submit">
                                        {{ __('Update') }}
                                    </button>

                                    <a class="btn btn-danger" href="{{ url()->previous() }}">
                                        {{ __('Cancel') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                @livewire('product-refill', ['product' => $product])
            </div>
        </div>
    </div>
@endsection

@pushonce('page-scripts')
    <script src="{{ asset('assets/js/img-preview.js') }}"></script>
@endpushonce
