@extends('layouts.tabler')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                    <div class="col-lg-6">
                        <livewire:tables.product-list :order_id="$order->id"/>
                    </div>

                    <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <h3 class="card-title">
                                    {{ __('Edit Order') }}
                                </h3>
                            </div>
                            <div class="card-actions btn-actions">
                                <x-action.close route="{{ route('orders.index') }}"/>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row gx-3 mb-3">
                                <div class="col-md-4">
                                    <label for="purchase_date" class="small my-1">
                                        {{ __('Date') }}
                                        <span class="text-danger">*</span>
                                    </label>

                                    <input name="purchase_date" id="purchase_date" type="date"
                                           class="form-control example-date-input @error('purchase_date') is-invalid @enderror"
                                           value="{{ old('purchase_date') ?? now()->format('Y-m-d') }}"
                                           required
                                    >

                                    @error('purchase_date')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="small mb-1" for="customer_id">
                                        {{ __('Customer') }}
                                        <span class="text-danger">*</span>
                                    </label>

                                    <select
                                        class="form-select form-control-solid @error('customer_id') is-invalid @enderror"
                                        id="customer_id" name="customer_id">
                                        <option selected="" disabled="">
                                            Select a customer:
                                        </option>
                                        @foreach ($customers as $customer)
                                            <option
                                                value="{{ $customer->id }}" @selected($order->customer_id == $customer->id)>
                                                {{ $customer->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error('customer_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="small mb-1" for="payment_type">
                                        {{ __('Payment') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control @error('payment_type') is-invalid @enderror"
                                            id="payment_type" name="payment_type">
                                        <option value="HandCash" @selected($order->payment_type == "HandCash")>
                                            Cash
                                        </option>
                                        <option value="Cheque" @selected($order->payment_type == "Cheque")>Cheque
                                        </option>
                                    </select>

                                    @error('payment_type')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="table-responsive">
                                <livewire:order-update :order_id="$order->id"/>
                            </div>

                        </div>
                        <div class="card-footer text-end">
                            <form action="{{ route('orders.update', $order->id) }}" method="POST">
                                @method('PUT')
                                @csrf
                                <button type="submit" class="btn btn-success add-list mx-1">
                                    {{ __('Done') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@pushonce('page-scripts')
    <script src="{{ asset('assets/js/img-preview.js') }}"></script>
@endpushonce
