<?php

namespace App\Http\Controllers\Quotation;

use App\Http\Controllers\Controller;
use App\Http\Requests\Quotation\StoreQuotationRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationDetails;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class QuotationController extends Controller
{
    public function index(): View
    {
        return view('quotations.index', [
            'quotations' => Quotation::where('user_id', auth()->id())->count(),
        ]);
    }

    public function create(): View
    {
        Cart::instance('quotation')->destroy();

        return view('quotations.create', [
            'cart' => Cart::content('quotation'),
            'products' => Product::where('user_id', auth()->id())->get(),
            'customers' => Customer::where('user_id', auth()->id())->get(),
        ]);
    }

    public function store(StoreQuotationRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $quotation = Quotation::create([
                'date' => $request->date,
                'reference' => $request->reference,
                'customer_id' => $request->customer_id,
                'customer_name' => Customer::findOrFail($request->customer_id)->name,
                'tax_percentage' => $request->tax_percentage,
                'discount_percentage' => $request->discount_percentage,
                'shipping_amount' => $request->shipping_amount,
                'total_amount' => $request->total_amount,
                'status' => $request->status,
                'note' => $request->note,
                'uuid' => Str::uuid(),
                'user_id' => auth()->id(),
                'tax_amount' => Cart::instance('quotation')->tax(),
                'discount_amount' => Cart::instance('quotation')->discount(),
            ]);

            foreach (Cart::instance('quotation')->content() as $cart_item) {
                QuotationDetails::create([
                    'quotation_id' => $quotation->id,
                    'product_id' => $cart_item->id,
                    'product_name' => $cart_item->name,
                    'product_code' => $cart_item->options->code,
                    'quantity' => $cart_item->qty,
                    'price' => $cart_item->price,
                    'unit_price' => $cart_item->options->unit_price,
                    'sub_total' => $cart_item->options->sub_total,
                    'product_discount_amount' => $cart_item->options->product_discount,
                    'product_discount_type' => $cart_item->options->product_discount_type,
                    'product_tax_amount' => $cart_item->options->product_tax,
                ]);
            }

            Cart::instance('quotation')->destroy();
        });

        return to_route('quotations.index')->with('success', 'Quotation Created!');
    }

    public function show(string $uuid): View
    {
        $quotation = Quotation::where('user_id', auth()->id())->where('uuid', $uuid)->firstOrFail();

        return view('quotations.show', [
            'quotation' => $quotation,
            'quotation_details' => QuotationDetails::where('quotation_id', $quotation->id)->get(),
        ]);
    }

    public function destroy(Quotation $quotation): RedirectResponse
    {
        $quotation->delete();

        return to_route('quotations.index');
    }

    public function update(string $uuid): RedirectResponse
    {
        $quotation = Quotation::where('user_id', auth()->id())->where('uuid', $uuid)->firstOrFail();

        $quotation->update(['status' => 1]);

        return to_route('quotations.index')->with('success', 'Quotation Completed!');
    }
}
