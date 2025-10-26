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
            font-family: 'Arial', sans-serif;
            font-size: 14px;
            color: #333;
        }

        .invoice-container {
            width: 21cm;
            min-height: 29.7cm;
            margin: 0 auto;
            background: white;
            position: relative;
        }

        .invoice-content {
            padding: 1.5cm 1.5cm 4cm 1.5cm; /* Bottom padding for footer */
            min-height: calc(29.7cm - 4cm);
        }

        /* Header with logo */
        .header-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-container {
            margin-bottom: 10px;
        }

        .logo-container img {
            height: 80px;
            width: auto;
        }

        .company-name {
            color: #003366;
            font-size: 18px;
            font-weight: bold;
            margin: 5px 0;
        }

        .company-name .electro {
            color: #cc0000;
        }

        /* Company and client info */
        .info-section {
            display: flex;
            justify-content: space-between;
            margin: 40px 0;
        }

        .company-info, .client-info {
            flex: 1;
        }

        .company-info {
            text-align: left;
        }

        .client-info {
            text-align: right;
        }

        .info-title {
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .info-text {
            font-size: 13px;
            line-height: 1.6;
            color: #555;
        }

        /* Invoice details */
        .invoice-details {
            margin: 30px 0;
        }

        .invoice-number {
            color: #cc0000;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .invoice-date {
            font-size: 14px;
            margin: 5px 0;
        }

        /* Table styles */
        .invoice-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 20px 0;
            margin-bottom: 30px;
            page-break-inside: auto;
        }
        

        .invoice-table thead {
            background-color: #f8f8f8;
            border-bottom: 2px solid #cc0000;
            page-break-after: avoid;
        }

        .invoice-table th {
            padding: 12px;
            text-align: left;
            font-weight: bold;
            font-size: 14px;
            color: #cc0000;
            border-bottom: 2px solid #cc0000;
        }

        .invoice-table tbody tr {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
            page-break-before: auto;
            page-break-after: auto;
            display: table-row;
            orphans: 3;
            widows: 3;
        }

        .invoice-table td {
            padding: 12px;
            font-size: 13px;
            vertical-align: top;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .invoice-table tfoot {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .text-center {
            text-align: center !important;
        }

        .text-right {
            text-align: right !important;
        }

        /* Total row */
        .total-row {
            border-top: 2px solid #cc0000;
            font-weight: bold;
            font-size: 16px;
            page-break-before: avoid;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .total-row td {
            padding: 15px 12px;
        }

        /* Footer */
        .invoice-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 10px 15px;
            text-align: center;
            border-top: 1px solid #e0e0e0;
            background: white;
            font-size: 11px;
            color: #666;
            height: 2.5cm;
        }

        .footer-text {
            line-height: 1.6;
        }

        /* Page number */
        .page-info {
            margin-top: 3px;
            font-size: 10px;
            color: #999;
        }

        /* Prevent overlap */
        @media print {
            @page {
                size: A4;
                margin: 1.5cm 1.5cm 3.5cm 1.5cm;
            }
            
            body {
                background: white;
                margin: 0;
                padding: 0;
            }
            
            .invoice-container {
                width: 100%;
                margin: 0;
                padding: 0;
            }
            
            .invoice-content {
                padding: 0.5cm 0;
                min-height: auto;
            }

            .header-section {
                margin-bottom: 20px;
            }
            
            .info-section {
                margin: 20px 0;
            }
            
            .invoice-details {
                margin: 20px 0;
            }
            
            .invoice-table {
                margin: 15px 0;
            }
            
            .invoice-table th,
            .invoice-table td {
                padding: 8px 12px;
            }

            .invoice-table tbody tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                display: table-row !important;
            }
            
            .invoice-table tbody tr td {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            
            .invoice-table thead {
                display: table-header-group;
            }
            
            .invoice-table tfoot {
                display: table-footer-group;
                page-break-inside: avoid;
            }
            
            .invoice-footer {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                width: 100%;
            }
        }

        /* Gift badge */
        .badge-gift {
            background-color: #28a745;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            margin-left: 5px;
        }
    </style>
</head>
<body>
<div class="invoice-container">
    <div class="invoice-content">
        <!-- Header with Logo -->
        <div class="header-section">
            <div class="logo-container">
                <img src="{{ asset('logo.jpeg') }}" alt="Platinium Electro Logo">
            </div>
            <div class="company-name">
                PLATINIUM <span class="electro">ELECTRO</span>
            </div>
        </div>

        <!-- Company and Client Information -->
        <div class="info-section">
            <div class="company-info">
                <div class="info-title">STE PLATINIUM ELECTRO</div>
                <div class="info-text">
                    AVENUE ATLAS TAHLA<br>
                    TAHLA<br>
                    Maroc
                </div>
            </div>
            <div class="client-info">
                <div class="info-title">Vendeur :</div>
                <div class="info-text">{{ $order->user->name }}</div>
            </div>
        </div>

        <!-- Invoice Details -->
        <div class="invoice-details">
            <div class="invoice-number">FACTURE : {{ $order->invoice_no }}</div>
            <div class="invoice-date">
                <strong>Date de facture :</strong> {{ $order->order_date->format('d M Y') }}
            </div>
        </div>

        <!-- Client Information -->
        <div class="info-section">
            <div class="company-info">
                <!-- Empty for alignment -->
            </div>
            <div class="client-info">
                <div class="info-title">Client :</div>
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
            </div>
        </div>

        <!-- Products Table -->
        <table class="invoice-table">
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
                <tr class="total-row">
                    <td colspan="3" class="text-right">Total</td>
                    <td class="text-right">{{ Number::currency($order->total, 'MAD') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    
    <!-- Footer - Fixed at bottom -->
    <div class="invoice-footer">
        <div class="footer-text">
            AVENUE ATLAS TAHLA - MAROC<br>
            Tél: +212 697-940615<br>
            ICE: 003299107000084 | IF: 53784335
        </div>
        <div class="page-info">
            Page 1 of 1
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
