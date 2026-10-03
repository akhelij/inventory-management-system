<?php

// Read-only report: order lines whose saved total is not quantity × unit price.
// These come from the order-create page submitting a price/quantity edit before the cart had saved it,
// so the line keeps the typed price but the old total (and the order total is built from the old totals).
// Run on the server with:
//   php artisan tinker database/scripts/find-mismatched-order-lines.php
// Nothing is written to the database.
use App\Enums\OrderStatus;
use Illuminate\Support\Facades\DB;

$lines = DB::table('order_details as d')
    ->join('orders as o', 'o.id', '=', 'd.order_id')
    ->leftJoin('customers as c', 'c.id', '=', 'o.customer_id')
    ->leftJoin('products as p', 'p.id', '=', 'd.product_id')
    ->whereRaw('ABS(d.total - d.quantity * d.unitcost) > 0.01')
    ->orderByDesc('o.id')
    ->get([
        'o.invoice_no', 'o.order_status', 'o.order_date', 'o.total as order_total', 'o.pay', 'o.due',
        'c.name as customer', 'p.code', 'p.name as product', 'p.selling_price',
        'd.quantity', 'd.unitcost', 'd.total',
    ]);

$status = fn ($s): string => match ($s) {
    OrderStatus::PENDING => 'PENDING',
    OrderStatus::APPROVED => 'APPROVED',
    OrderStatus::CANCELED => 'CANCELED',
    default => (string) $s,
};

$money = fn ($v): string => number_format((float) $v, 2, '.', '');

echo implode("\t", ['invoice', 'status', 'date', 'customer', 'code', 'product', 'qty', 'unit_price', 'line_total', 'qty_x_price', 'missing', 'current_selling_price', 'order_total', 'paid', 'due'])."\n";

$lines->each(function ($l) use ($status, $money): void {
    $expected = $l->quantity * $l->unitcost;

    echo implode("\t", [
        $l->invoice_no, $status($l->order_status), $l->order_date, $l->customer, $l->code, $l->product,
        $l->quantity, $money($l->unitcost), $money($l->total), $money($expected), $money($expected - $l->total),
        $money($l->selling_price), $money($l->order_total), $money($l->pay), $money($l->due),
    ])."\n";
});

$approved = $lines->filter(fn ($l) => $l->order_status === OrderStatus::APPROVED);

echo "\n{$lines->count()} mismatched lines in {$lines->unique('invoice_no')->count()} orders"
    ." ({$approved->unique('invoice_no')->count()} approved).\n";
echo 'Total missing on approved orders (qty × price − saved total): '
    .$money($approved->sum(fn ($l) => $l->quantity * $l->unitcost - $l->total))."\n";
