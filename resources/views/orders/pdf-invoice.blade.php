<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $order->invoice_no }}</title>
    <style>
        @page {
            margin: 1.5cm 2cm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11pt;
            color: #333;
            line-height: 1.4;
            padding: 0 1cm;
        }

        .header-section {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #cc0000;
        }

        .logo-img {
            height: 80px;
            margin-bottom: 10px;
        }

        .logo-text {
            font-size: 20pt;
            font-weight: bold;
            color: #003366;
            margin-bottom: 5px;
        }

        .logo-text .electro {
            color: #cc0000;
        }

        .company-subtitle {
            font-size: 9pt;
            color: #666;
            line-height: 1.6;
        }

        .invoice-title {
            color: #cc0000;
            font-size: 18pt;
            font-weight: bold;
            margin: 15px 0 10px 0;
        }

        .invoice-date {
            font-size: 10pt;
            margin-bottom: 15px;
        }

        .info-row {
            width: 100%;
            margin: 15px 0;
        }

        .info-row table {
            width: 100%;
        }

        .info-box {
            vertical-align: top;
            padding: 10px;
        }

        .info-box.left {
            text-align: left;
            width: 50%;
        }

        .info-box.right {
            text-align: right;
            width: 50%;
        }

        .info-label {
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 5px;
        }

        .info-text {
            font-size: 10pt;
            color: #555;
            line-height: 1.5;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .products-table thead th {
            background-color: #f8f8f8;
            color: #cc0000;
            font-weight: bold;
            padding: 10px 8px;
            text-align: left;
            border-bottom: 2px solid #cc0000;
            font-size: 10pt;
        }

        .products-table tbody tr {
            page-break-inside: avoid;
        }

        .products-table tbody td {
            padding: 10px 8px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 10pt;
        }

        .products-table tfoot tr {
            page-break-inside: avoid;
        }

        .products-table tfoot td {
            padding: 12px 8px;
            font-weight: bold;
            font-size: 12pt;
            border-top: 2px solid #cc0000;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .badge-gift {
            background-color: #28a745;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8pt;
            margin-left: 5px;
        }

    </style>
</head>
<body>
    <!-- Header -->
    <div class="header-section">
        <img src="{{ public_path('logo.jpeg') }}" alt="Logo" class="logo-img">
        <div class="logo-text">
            PLATINIUM <span class="electro">ELECTRO</span>
        </div>
        <div class="company-subtitle">
            AVENUE ATLAS TAHLA - MAROC<br>
            Tél: +212 697-940615<br>
            ICE: 003299107000084 | IF: 53784335
        </div>
    </div>

    <!-- Invoice Title and Date -->
    <div class="invoice-title">FACTURE : {{ $order->invoice_no }}</div>
    <div class="invoice-date">
        <strong>Date de facture :</strong> {{ $order->order_date->format('d M Y') }}
    </div>

    <!-- Company and Customer Info -->
    <div class="info-row">
        <table>
            <tr>
                <td class="info-box left">
                    <div class="info-label">Vendeur :</div>
                    <div class="info-text">{{ $order->user->name }}</div>
                </td>
                <td class="info-box right">
                    <div class="info-label">Client :</div>
                    <div class="info-text">
                        {{ $order->customer->name }}<br>
                        @if($order->customer->phone)
                            {{ $order->customer->phone }}<br>
                        @endif
                        @if($order->customer->email)
                            {{ $order->customer->email }}<br>
                        @endif
                        @if($order->customer->address)
                            {{ $order->customer->address }}
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Products Table -->
    <table class="products-table">
        <thead>
            <tr>
                <th style="width: 50%">Description</th>
                <th class="text-center" style="width: 15%">Quantité</th>
                <th class="text-right" style="width: 17.5%">Prix unitaire</th>
                <th class="text-right" style="width: 17.5%">Prix total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->details as $item)
                <tr>
                    <td>
                        {{ $item->product->name }}
                        @if($item->unitcost == 0)
                            <span class="badge-gift">Gift</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ Number::currency($item->unitcost, 'MAD') }}</td>
                    <td class="text-right">{{ Number::currency($item->total, 'MAD') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right">Total</td>
                <td class="text-right">{{ Number::currency($order->total, 'MAD') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
