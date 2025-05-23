<!DOCTYPE html>
<html lang="en">

<head>
    <title>
        Alami Gestion
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <!-- External CSS libraries -->
    <link type="text/css" rel="stylesheet" href="{{ asset('assets/invoice/css/bootstrap.min.css') }}">
    <link type="text/css" rel="stylesheet"
        href="{{ asset('assets/invoice/fonts/font-awesome/css/font-awesome.min.css') }}">
    <!-- Google fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <!-- Custom Stylesheet -->
    <link type="text/css" rel="stylesheet" href="{{ asset('assets/invoice/css/style.css') }}">
</head>

<body>
    <div class="invoice-16 invoice-content">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="invoice-inner-9" id="invoice_wrapper" style="position:relative; height: 340vh; display: flex; align-items: center; flex-direction: column">
                        <div class="invoice-top w-100">
                            <div class="row">
                                <div class="logo"
                                     style=" display: flex; justify-content: center;  align-items: center; margin-bottom: 40px">
                                    <img src="{{ asset('logo.jpeg') }}" alt="logo" style="height:100px;">
                                </div>
                            </div>
                        </div>
                        <div class="order-summary w-100">
                            <div class="table-outer">
                                <table class="default-table invoice-table">
                                    <thead>
                                        <tr>
                                            <th class="align-middle">Date</th>
                                            <th class="align-middle">Client</th>
                                            <th class="align-middle">Vendeur</th>
                                            <th class="align-middle">Produit</th>
                                            <th class="align-middle text-center">Quantité</th>
                                            <th class="align-middle text-center">Prix unitaire</th>
                                            <th class="align-middle text-center">Prix total</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                    @foreach ($orders as $order)
                                        @foreach ($order->details as $item)
                                            <tr>
                                                <td class="align-middle">
                                                    {{ $order->order_date->format('Y-m-d') }}
                                                </td>
                                                <td class="align-middle">
                                                    {{ $order->customer?->name }}
                                                </td>
                                                <td class="align-middle">
                                                    {{ $order->user?->name }}
                                                </td>
                                                <td class="align-middle">
                                                    {{ $item->product->name }}
                                                    @if($item->unitcost == 0)
                                                        <span class="badge bg-success" style="font-size: 10px; padding: 3px 5px;">Gift</span>
                                                    @endif
                                                </td>
                                                <td class="align-middle text-center">
                                                    {{ $item->quantity }}
                                                </td>
                                                <td class="align-middle text-center">
                                                    {{ Number::currency($item->unitcost, 'MAD') }}
                                                </td>
                                                <td class="align-middle text-center">
                                                    {{ Number::currency($item->total, 'MAD') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="invoice-information-footer row">
                            <p class="inv-from-1" style="text-align: center">
                                AVENUE ATLAS TAHLA - MAROC<br/>
                                Tél: +212 697-940615<br/>
                                ICE: 003299107000084 | IF: 53784335
                            </p>
                        </div>
                    </div>

                    <div class="invoice-btn-section clearfix d-print-none">
                        <a href="{{ route('orders.index') }}" class="btn btn-lg btn-print">
                            <i class="fa fa-arrow-left"></i>
                            Retour
                        </a>
                        <a id="invoice_download_btn" class="btn btn-lg btn-download">
                            <i class="fa fa-download"></i>
                            Telecharger la facture
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('assets/invoice/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/invoice/js/jspdf.min.js') }}"></script>
    <script src="{{ asset('assets/invoice/js/html2canvas.js') }}"></script>
    <script src="{{ asset('assets/invoice/js/app.js') }}"></script>
</body>

</html>
