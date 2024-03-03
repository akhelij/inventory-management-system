@extends('layouts.tabler')

@section('content')
<div class="page-body">
    <div class="container-xl">
        <x-alert/>

        <div class="row row-cards">
            <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <h3 class="card-title">
                                    {{ __('products.product_image') }}
                                </h3>

                                <img class="img-account-profile mb-2" src="{{ asset('assets/img/products/default.webp') }}" alt="" id="image-preview" />

                                <div class="small font-italic text-muted mb-2">
                                    {{ __('products.image_info') }}
                                </div>

                                <input
                                    type="file"
                                    accept="image/*"
                                    id="image"
                                    name="product_image"
                                    class="form-control @error('product_image') is-invalid @enderror"
                                    onchange="previewImage();"
                                >

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
                            <div class="card-header">
                                <div>
                                    <h3 class="card-title">
                                        {{ __('products.product_create') }}
                                    </h3>
                                </div>

                                <div class="card-actions">
                                    <a href="{{ route('products.index') }}" class="btn-action">
                                        <!-- Icon -->
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row row-cards">
                                    <div class="col-md-12">

                                        <x-input name="name"
                                                 id="name"
                                                 placeholder="{{ __('products.product_name') }}"
                                                 value="{{ old('name') }}"
                                        />
                                    </div>

                                    <div class="col-sm-6 col-md-6">
                                        <div class="mb-3">
                                            <label for="category_id" class="form-label">
                                                {{ __('products.product_category') }}
                                                <span class="text-danger">*</span>
                                            </label>

                                            @if ($categories->count() === 1)
                                                <select name="category_id" id="category_id"
                                                        class="form-select @error('category_id') is-invalid @enderror"
                                                        readonly
                                                >
                                                    @foreach ($categories as $category)
                                                        <option value="{{ $category->id }}" selected>
                                                            {{ $category->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <select name="category_id" id="category_id"
                                                        class="form-select @error('category_id') is-invalid @enderror"
                                                >
                                                    <option selected="" disabled="">
                                                        {{ __('products.select_category') }}
                                                    </option>

                                                    @foreach ($categories as $category)
                                                        <option value="{{ $category->id }}" @if(old('category_id') == $category->id) selected="selected" @endif>
                                                            {{ $category->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @endif

                                            @error('category_id')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Other fields -->

                                </div>
                            </div>

                            <div class="card-footer text-end">
                                <x-button.save type="submit">
                                    {{ __('Save') }}
                                </x-button.save>

                                <a class="btn btn-warning" href="{{ url()->previous() }}">
                                    {{ __('Cancel') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@pushonce('page-scripts')
    <script src="{{ asset('assets/js/img-preview.js') }}"></script>
@endpushonce
