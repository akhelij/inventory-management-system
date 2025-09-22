@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">{{ __('Trashed Products') }}</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('products.index') }}">{{ __('Products') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Trashed') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title">{{ __('Trashed Products') }}</h4>
                            <p class="card-title-desc">{{ __('Manage soft deleted products - restore or permanently delete them.') }}</p>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('products.index') }}" class="btn btn-primary">
                                <i class="fas fa-arrow-left me-1"></i>
                                {{ __('Back to Products') }}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($trashedProducts > 0)
                        <livewire:power-grid.trashed-products-table />
                    @else
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="fas fa-trash-alt fa-3x text-muted"></i>
                            </div>
                            <h5 class="text-muted">{{ __('No trashed products found') }}</h5>
                            <p class="text-muted">{{ __('All products are currently active.') }}</p>
                            <a href="{{ route('products.index') }}" class="btn btn-primary">
                                {{ __('View Active Products') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
