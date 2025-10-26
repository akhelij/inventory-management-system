<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport des Commandes</title>
    <style>
        @page {
            margin: 1.5cm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.4;
        }

        .header-section {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #cc0000;
        }

        .logo-text {
            font-size: 18pt;
            font-weight: bold;
            color: #003366;
            margin-bottom: 5px;
        }

        .logo-text .electro {
            color: #cc0000;
        }

        .report-title {
            font-size: 16pt;
            font-weight: bold;
            color: #cc0000;
            margin: 15px 0;
            text-align: center;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .orders-table thead th {
            background-color: #f8f8f8;
            color: #cc0000;
            font-weight: bold;
            padding: 8px 6px;
            text-align: left;
            border-bottom: 2px solid #cc0000;
            font-size: 9pt;
        }

        .orders-table tbody tr {
            page-break-inside: avoid;
        }

        .orders-table tbody td {
            padding: 8px 6px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 9pt;
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
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 7pt;
            margin-left: 5px;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9pt;
            color: #666;
            padding: 10px 0;
            border-top: 1px solid #e0e0e0;
        }

        .footer-content {
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header-section">
        <div class="logo-text">
            PLATINIUM <span class="electro">ELECTRO</span>
        </div>
    </div>

    <!-- Report Title -->
    <div class="report-title">RAPPORT DES COMMANDES</div>

    <!-- Orders Table -->
    <table class="orders-table">
        <thead>
            <tr>
                <th style="width: 10%">Date</th>
                <th style="width: 15%">Client</th>
                <th style="width: 13%">Vendeur</th>
                <th style="width: 30%">Produit</th>
                <th class="text-center" style="width: 8%">Quantité</th>
                <th class="text-right" style="width: 12%">Prix unitaire</th>
                <th class="text-right" style="width: 12%">Prix total</th>
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

    <!-- Footer -->
    <div class="footer">
        <div class="footer-content">
            AVENUE ATLAS TAHLA - MAROC | Tél: +212 697-940615<br>
            ICE: 003299107000084 | IF: 53784335
        </div>
    </div>
</body>
</html>
