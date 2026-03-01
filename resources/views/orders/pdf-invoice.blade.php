<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture {{ $order->invoice_no }}</title>
    <style>
        @page {
            margin: 1.5cm 1cm 3cm 1cm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9pt;
            color: #333;
            line-height: 1.4;
            padding: 0 1.5cm;
        }

        /* ── Header ── */
        .header {
            text-align: center;
            margin-bottom: 35px;
        }

        .logo-img {
            height: 55px;
        }

        /* ── Info Section ── */
        .info-section {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
        }

        .info-section td {
            vertical-align: bottom;
        }

        .section-title {
            font-size: 11pt;
            font-weight: bold;
            color: #333;
            margin-bottom: 6px;
        }

        .field-table {
            border-collapse: collapse;
            width: 100%;
        }

        .field-table td {
            font-size: 9pt;
            padding: 2px 0;
            color: #333;
        }

        .field-label {
            font-size: 8pt;
            text-transform: uppercase;
            color: #666;
            white-space: nowrap;
            padding-right: 8px;
        }

        .field-value {
            font-size: 9pt;
            color: #333;
        }

        .client-box {
            border: 1px solid #999;
            padding: 8px 10px;
        }

        .client-name {
            font-size: 10pt;
            color: #333;
            margin-bottom: 2px;
        }

        .client-detail {
            font-size: 9pt;
            color: #555;
            line-height: 1.5;
        }

        /* ── Products Table ── */
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .products-table thead th {
            background-color: #003366;
            color: #ffffff;
            font-weight: bold;
            padding: 10px 8px;
            text-align: left;
            font-size: 9pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .products-table thead th:first-child {
            border-radius: 4px 0 0 0;
        }

        .products-table thead th:last-child {
            border-radius: 0 4px 0 0;
        }

        .products-table tbody tr {
            page-break-inside: avoid;
        }

        .products-table tbody td {
            padding: 9px 8px;
            border-bottom: 1px solid #e9ecef;
            font-size: 9.5pt;
        }

        .products-table tbody tr.row-even td {
            background-color: #f8f9fa;
        }

        .badge-gift {
            background-color: #00b894;
            color: #ffffff;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-left: 5px;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }

        /* ── Totals Section ── */
        .totals-wrapper {
            page-break-inside: avoid;
            margin-top: 10px;
        }

        .totals-table {
            width: 280px;
            margin-left: auto;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 8px 12px;
            font-size: 10pt;
        }

        .totals-table .label {
            text-align: right;
            color: #636e72;
            font-weight: bold;
        }

        .totals-table .value {
            text-align: right;
            font-weight: bold;
            width: 120px;
        }

        .totals-table .total-row td {
            border-top: 2px solid #003366;
            font-size: 12pt;
            color: #003366;
            padding-top: 10px;
        }

        .totals-table .paid-row td {
            color: #00b894;
        }

        .totals-table .due-row td {
            border-top: 1px solid #dfe6e9;
            padding-top: 10px;
        }

        .due-red {
            color: #cc0000 !important;
            font-size: 12pt;
        }

        .due-green {
            color: #00b894 !important;
            font-size: 12pt;
        }

        /* ── Footer ── */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            padding: 10px 2cm;
            border-top: 2px solid #cc0000;
            font-size: 8pt;
            color: #636e72;
            line-height: 1.6;
            background-color: #ffffff;
        }

        .footer-company {
            font-weight: bold;
            color: #003366;
            font-size: 9pt;
        }
    </style>
</head>
<body>
    <!-- Footer -->
    <div class="footer">
        <div class="footer-company">ALAMI ELECTRO</div>
        AVENUE ATLAS TAHLA - MAROC &bull; T&eacute;l: +212 697-940615<br>
        ICE: 003299107000084 &bull; IF: 53784335
    </div>

    <!-- Header -->
    <div class="header">
        <img src="{{ public_path('logo.jpeg') }}" alt="Logo" class="logo-img">
    </div>

    <!-- Facture title + Info -->
    <table class="info-section">
        <tr>
            <td style="width: 50%; padding-right: 20px;">
                <div class="section-title">Facture</div>

                <table class="field-table">
                    <tr>
                        <td class="field-label">Num&eacute;ro :</td>
                        <td class="field-value">{{ $order->invoice_no }}</td>
                    </tr>
                    <tr>
                        <td class="field-label">Date :</td>
                        <td class="field-value">{{ $order->order_date->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="field-label">Vendeur :</td>
                        <td class="field-value">{{ $order->user->name }}</td>
                    </tr>
                </table>
            </td>
            <td style="width: 50%;">
                <div class="section-title">Client :</div>
                <div class="client-box">
                    <div class="client-name">{{ $order->customer->name }}</div>
                    <div class="client-detail">
                        @if($order->customer->address)
                            {{ $order->customer->address }}<br>
                        @endif
                        @if($order->customer->phone)
                            {{ $order->customer->phone }}
                        @endif
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Products Table -->
    <table class="products-table">
        <thead>
            <tr>
                <th style="width: 50%">Description</th>
                <th class="text-center" style="width: 12%">Quantit&eacute;</th>
                <th class="text-right" style="width: 19%">Prix unitaire</th>
                <th class="text-right" style="width: 19%">Prix total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->details as $index => $item)
                <tr class="{{ $index % 2 === 1 ? 'row-even' : '' }}">
                    <td>
                        {{ $item->product->name }}
                        @if($item->unitcost == 0)
                            <span class="badge-gift">Cadeau</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->unitcost, 2, ',', ' ') }}</td>
                    <td class="text-right">{{ number_format($item->total, 2, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals-wrapper">
        <table class="totals-table">
            <tr class="total-row">
                <td class="label">Sous-total</td>
                <td class="value">{{ Number::currency($order->total, 'MAD') }}</td>
            </tr>
            <tr class="paid-row">
                <td class="label">Total Pay&eacute;</td>
                <td class="value">{{ Number::currency($order->pay, 'MAD') }}</td>
            </tr>
            <tr class="due-row">
                <td class="label">Reste &agrave; payer</td>
                <td class="value {{ $order->due > 0 ? 'due-red' : 'due-green' }}">
                    {{ Number::currency($order->due, 'MAD') }}
                </td>
            </tr>
        </table>
    </div>

    <!-- Closing statement -->
    <div style="margin-top: 25px; font-size: 9pt; color: #333; font-style: italic; page-break-inside: avoid;">
        Arr&ecirc;t&eacute;e la pr&eacute;sente facture &agrave; la somme de :<br>
        <strong>{{ amount_in_french_words($order->total) }}</strong>.
    </div>
</body>
</html>
