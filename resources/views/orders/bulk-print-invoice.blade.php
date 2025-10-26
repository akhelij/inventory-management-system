<!DOCTYPE html>
<html lang="en">
<head>
    <title>Alami Gestion - Rapport</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <link type="text/css" rel="stylesheet" href="{{ asset('assets/invoice/css/bootstrap.min.css') }}">
    <link type="text/css" rel="stylesheet" href="{{ asset('assets/invoice/fonts/font-awesome/css/font-awesome.min.css') }}">
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
            display: flex;
            flex-direction: column;
        }

        .invoice-content {
            flex: 1;
            padding: 2cm 1.5cm 1cm 1.5cm;
            padding-bottom: 3cm;
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

        .report-title {
            font-size: 20px;
            font-weight: bold;
            color: #cc0000;
            margin: 20px 0;
            text-align: center;
        }

        /* Table styles */
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
            page-break-inside: auto;
        }

        .invoice-table thead {
            background-color: #f8f8f8;
            border-bottom: 2px solid #cc0000;
            page-break-after: avoid;
        }

        .invoice-table th {
            padding: 10px;
            text-align: left;
            font-weight: bold;
            font-size: 13px;
            color: #cc0000;
        }

        .invoice-table tbody tr {
            border-bottom: 1px solid #e0e0e0;
            page-break-inside: avoid;
            break-inside: avoid;
            page-break-before: auto;
            page-break-after: auto;
        }

        .invoice-table td {
            padding: 10px;
            font-size: 12px;
            vertical-align: top;
        }

        .text-center {
            text-align: center !important;
        }

        .text-right {
            text-align: right !important;
        }

        /* Footer */
        .invoice-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 20px;
            text-align: center;
            border-top: 2px solid #e0e0e0;
            background: white;
            font-size: 12px;
            color: #666;
        }

        .footer-text {
            line-height: 1.6;
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

        /* Print buttons */
        .invoice-btn-section {
            margin: 20px 0;
            text-align: center;
        }

        .btn {
            margin: 0 10px;
        }

        @media print {
            body {
                background: white;
            }

            .invoice-table tbody tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            
            .invoice-table thead {
                display: table-header-group;
            }

            .invoice-content {
                min-height: calc(29.7cm - 3cm);
            }

            .invoice-btn-section {
                display: none;
            }
        }
    </style>
</head>

<body>
<div class="invoice-container" id="invoice_wrapper">
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

        <!-- Report Title -->
        <div class="report-title">
            RAPPORT DES COMMANDES
        </div>

        <!-- Orders Table -->
        <table class="invoice-table">
            <thead>
                <tr>
                    <th style="width: 12%">Date</th>
                    <th style="width: 18%">Client</th>
                    <th style="width: 15%">Vendeur</th>
                    <th style="width: 25%">Produit</th>
                    <th class="text-center" style="width: 10%">Quantité</th>
                    <th class="text-right" style="width: 10%">Prix unitaire</th>
                    <th class="text-right" style="width: 10%">Prix total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orders as $order)
                    @foreach ($order->details as $item)
                        <tr>
                            <td>{{ $order->order_date->format('Y-m-d') }}</td>
                            <td>{{ $order->customer?->name }}</td>
                            <td>{{ $order->user?->name }}</td>
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
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="invoice-footer">
        <div class="footer-text">
            AVENUE ATLAS TAHLA - MAROC<br>
            Tél: +212 697-940615<br>
            ICE: 003299107000084 | IF: 53784335
        </div>
    </div>
</div>

<!-- Print buttons -->
<div class="invoice-btn-section d-print-none">
    <a href="{{ route('orders.index') }}" class="btn btn-lg btn-secondary">
        <i class="fa fa-arrow-left"></i> Retour
    </a>
    <a id="invoice_download_btn" class="btn btn-lg btn-primary">
        <i class="fa fa-download"></i> Télécharger la facture
    </a>
</div>

<script src="{{ asset('assets/invoice/js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/invoice/js/jspdf.min.js') }}"></script>
<script src="{{ asset('assets/invoice/js/html2canvas.js') }}"></script>
<script src="{{ asset('assets/invoice/js/app.js') }}"></script>
</body>

</html>
