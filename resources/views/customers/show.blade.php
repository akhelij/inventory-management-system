@extends('layouts.tabler')

@section('content')

    <div class="page-header d-print-none">
        <div class="container-xl">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="row g-2 align-items-center mb-3">
                <div class="col">
                    <h2 class="page-title">
                        {{ $customer->name }}
                    </h2>
                </div>
            </div>

            @include('partials._breadcrumbs', ['model' => $customer])
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="row">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                {{ __('Customer Details') }}
                            </h3>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <tbody>
                                        <tr>
                                            <td>{{ __('Name') }}</td>
                                            <td>{{ $customer->name }}</td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Email address') }}</td>
                                            <td>{{ $customer->email }}</td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Phone number') }}</td>
                                            <td>{{ $customer->phone }}</td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Address') }}</td>
                                            <td>{{ $customer->address }}</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <tbody>
                                        <tr>
                                            <td>{{ __('Limit') }}</td>
                                            <td>{{ Number::currency($customer->limit, 'MAD') }}</td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Account holder') }}</td>
                                            <td>{{ $customer->account_holder }}</td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Account number') }}</td>
                                            <td>{{ $customer->account_number }}</td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Bank name') }}</td>
                                            <td>{{ $customer->bank_name }}</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <a class="btn btn-info" href="{{ route('customers.index') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrow-left"
                                     width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                     fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M5 12l14 0"/>
                                    <path d="M5 12l6 6"/>
                                    <path d="M5 12l6 -6"/>
                                </svg>
                                {{ __('Back') }}
                            </a>

                            <a class="btn btn-warning" href="{{ route('customers.edit', $customer->uuid) }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-pencil"
                                     width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                     fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4"/>
                                    <path d="M13.5 6.5l4 4"/>
                                </svg>
                                {{ __('Edit') }}
                            </a>
                        </div>
                    </div>
                </div>

            </div>

            <div class="row" style="margin-left:-20px; margin-top:1%">
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                {{ __('Orders') }}
                            </h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($customer->orders as $order)
                                    <tr>
                                        <td>{{$order->created_at}}</td>
                                        <td>{{ Number::currency($order->total, 'MAD') }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <h3 class="card-title">
                                    {{ __('Payments') }}
                                </h3>
                            </div>

                            <div class="card-actions">
                                <x-action.create route="{{ '/payments/'.$customer->id.'/create'}}"/>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($customer->payments as $payment)
                                    <tr>
                                        <td>{{$payment->created_at}}</td>
                                        <td>{{ Number::currency($payment->amount, 'MAD') }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
