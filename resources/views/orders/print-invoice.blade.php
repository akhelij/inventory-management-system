<!DOCTYPE html>
<html lang="en">
<head>
    <title>Alami Gestion</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <link type="text/css" rel="stylesheet" href="{{ asset('assets/invoice/css/bootstrap.min.css') }}">
    <link type="text/css" rel="stylesheet" href="{{ asset('assets/invoice/fonts/font-awesome/css/font-awesome.min.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="{{ asset('assets/invoice/css/style.css') }}">
    <style>
        @page {
            size: A4;
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
            background: #f8f9fa;
        }

        .invoice-16 {
            padding: 0;
            background: white;
            min-height: 100vh;
            display: flex;
            justify-content: center;
        }

        .invoice-16 .invoice-inner-9 {
            width: 21cm;
            min-height: 29.7cm;
            position: relative;
            padding: 1.5cm 1cm;
            margin: 0;
            background: white;
        }

        .invoice-16 .invoice-top {
            padding: 0 30px 20px;
        }

        .invoice-16 .invoice-info {
            padding: 0 30px 20px;
        }

        .invoice-16 .order-summary {
            padding: 0 30px;
        }

        .invoice-16 .default-table {
            margin-bottom: 50px;
        }

        .invoice-16 .default-table td,
        .invoice-16 .default-table th {
            border: 1px solid #ECEDF2;
        }

        .invoice-16 .default-table thead th {
            background: #F5F7FC;
            color: #910706;
            font-weight: 500;
            text-align: left;
            padding: 15px;
        }

        .invoice-16 .default-table tbody td {
            padding: 15px;
        }

        .invoice-16 .invoice-information-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            border-top: 1px solid #ECEDF2;
            text-align: center;
        }

        @media print {
            body {
                background: white;
            }

            .invoice-16 {
                padding: 0;
                height: 100%;
            }

            .invoice-16 .invoice-inner-9 {
                box-shadow: none;
            }

            .invoice-btn-section {
                display: none;
            }
        }
    </style>
</head>
<body>
<div class="invoice-16 invoice-content">
    <div class="invoice-inner-9">
        <div class="invoice-top">
            <div class="row">
                <div class="logo" style="display: flex; justify-content: center; align-items: center; margin-bottom: 40px">
                    <img src="{{ asset('logo.jpeg') }}" alt="logo" style="height:100px;">
                </div>
            </div>
            <div class="row">
                <div class="col-sm-9">
                    <h4 class="inv-title-1">STE PLATINIUM ELECTRO</h4>
                    <p class="inv-from-1">AVENUE ATLAS TAHLA <br/>TAHLA<br/>Maroc</p>
                </div>
                <div class="col-sm-3">
                    <h4 class="inv-title-1">Vendeur :</h4>
                    <p class="inv-from-1">{{ $order->user->name }}</p>
                </div>
            </div>
        </div>
        <div class="invoice-info">
            <div class="row">
                <div class="col-sm-9">
                    <div class="invoice">
                        <h4 style="color:#910706">
                            FACTURE : <span>{{ $order->invoice_no }}</span>
                        </h4>
                    </div>
                    <div class="invoice-number">
                        <h4 class="inv-title-1">Date de facture :</h4>
                        <p class="invo-addr-1">{{ $order->order_date->format('d M Y') }}</p>
                    </div>
                </div>
                <div class="col-sm-3">
                    <h4 class="inv-title-1">Client :</h4>
                    <p class="inv-from-1">{{ $order->customer->name }}</p>
                    <p class="inv-from-1">{{ $order->customer->phone }}</p>
                    <p class="inv-from-1">{{ $order->customer->email }}</p>
                    <p class="inv-from-2">{{ $order->customer->address }}</p>
                </div>
            </div>
        </div>
        <div class="order-summary">
            <div class="table-outer">
                <table class="default-table invoice-table">
                    <thead>
                    <tr>
                        <th style="width: 40%">Description</th>
                        <th class="text-center" style="width: 20%">Quantité</th>
                        <th class="text-center" style="width: 20%">Prix unitaire</th>
                        <th class="text-center" style="width: 20%">Prix total</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($order->details as $item)
                        <tr>
                            <td>
                                {{ $item->product->name }}
                                @if($item->unitcost == 0)
                                    <span class="badge bg-success" style="font-size: 10px; padding: 3px 5px;">Gift</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-center">{{ Number::currency($item->unitcost, 'MAD') }}</td>
                            <td class="text-center">{{ Number::currency($item->total, 'MAD') }}</td>
                        </tr>
                    @endforeach
{{--                    <tr>--}}
{{--                        <td colspan="3" class="text-end"><strong>Total HT</strong></td>--}}
{{--                        <td class="text-center">--}}
{{--                            <strong>{{ Number::currency($order->sub_total, 'MAD') }}</strong>--}}
{{--                        </td>--}}
{{--                    </tr>--}}
{{--                    <tr>--}}
{{--                        <td colspan="3" class="text-end"><strong>Tax</strong></td>--}}
{{--                        <td class="text-center">--}}
{{--                            <strong>{{ Number::currency($order->vat, 'MAD') }}</strong>--}}
{{--                        </td>--}}
{{--                    </tr>--}}
                    <tr>
                        <td colspan="3" class="text-end"><strong>Total</strong></td>
                        <td class="text-center">
                            <strong>{{ Number::currency($order->total, 'MAD') }}</strong>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="invoice-information-footer">
            <p class="inv-from-1">
                AVENUE ATLAS TAHLA - MAROC<br/>
                Tél: +212 697-940615<br/>
                ICE: 003299107000084 | IF: 53784335
            </p>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            window.print();
        }, 500);
    });
</script>
</body>
</html>
