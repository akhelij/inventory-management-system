<?php

namespace App\Http\Controllers\Purchase;

use App\Enums\PurchaseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Purchase\StorePurchaseRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetails;
use App\Models\Supplier;
use Carbon\Carbon;
use Exception;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

class PurchaseController extends Controller
{
    public function index(): View
    {
        return view('purchases.index', [
            'purchases' => Purchase::where('user_id', auth()->id())->count(),
        ]);
    }

    public function approvedPurchases(): View
    {
        return view('purchases.approved-purchases', [
            'purchases' => Purchase::with('supplier')->where('status', PurchaseStatus::APPROVED)->get(),
        ]);
    }

    public function show(string $uuid): View
    {
        $purchase = Purchase::where('uuid', $uuid)->firstOrFail();
        $purchase->loadMissing(['supplier', 'details']);

        return view('purchases.show', [
            'purchase' => $purchase,
        ]);
    }

    public function edit(string $uuid): View
    {
        $purchase = Purchase::where('uuid', $uuid)->with(['supplier', 'details'])->firstOrFail();

        return view('purchases.edit', [
            'purchase' => $purchase,
        ]);
    }

    public function create(): View
    {
        return view('purchases.create', [
            'categories' => Category::where('user_id', auth()->id())->select(['id', 'name'])->get(),
            'suppliers' => Supplier::where('user_id', auth()->id())->select(['id', 'name'])->get(),
        ]);
    }

    public function store(StorePurchaseRequest $request): RedirectResponse
    {
        $purchase = Purchase::create([
            'purchase_no' => IdGenerator::generate([
                'table' => 'purchases',
                'field' => 'purchase_no',
                'length' => 10,
                'prefix' => 'PRS-',
            ]),
            'status' => PurchaseStatus::PENDING->value,
            'created_by' => auth()->id(),
            'supplier_id' => $request->supplier_id,
            'date' => $request->date,
            'total_amount' => $request->total_amount,
            'uuid' => Str::uuid(),
            'user_id' => auth()->id(),
        ]);

        if ($request->invoiceProducts) {
            foreach ($request->invoiceProducts as $product) {
                $purchase->details()->insert([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product['product_id'],
                    'quantity' => $product['quantity'],
                    'unitcost' => $product['unitcost'],
                    'total' => $product['total'],
                    'created_at' => Carbon::now(),
                ]);
            }
        }

        return to_route('purchases.index')->with('success', 'Purchase has been created!');
    }

    public function update(string $uuid, Request $request): RedirectResponse
    {
        $purchase = Purchase::where('uuid', $uuid)->firstOrFail();
        $products = PurchaseDetails::where('purchase_id', $purchase->id)->get();

        foreach ($products as $product) {
            Product::where('id', $product->product_id)
                ->update(['quantity' => DB::raw('quantity+'.$product->quantity)]);
        }

        $purchase->update([
            'status' => PurchaseStatus::APPROVED,
            'updated_by' => auth()->id(),
        ]);

        return to_route('purchases.index')->with('success', 'Purchase has been approved!');
    }

    public function destroy(string $uuid): RedirectResponse
    {
        Purchase::where('uuid', $uuid)->firstOrFail()->delete();

        return to_route('purchases.index')->with('success', 'Purchase has been deleted!');
    }

    public function dailyPurchaseReport(): View
    {
        return view('purchases.details-purchase', [
            'purchases' => Purchase::with('supplier')->where('date', today()->format('Y-m-d'))->get(),
        ]);
    }

    public function getPurchaseReport(): View
    {
        return view('purchases.report-purchase');
    }

    public function exportPurchaseReport(Request $request): void
    {
        $validated = $request->validate([
            'start_date' => 'required|string|date_format:Y-m-d',
            'end_date' => 'required|string|date_format:Y-m-d',
        ]);

        $purchases = DB::table('purchase_details')
            ->join('products', 'purchase_details.product_id', '=', 'products.id')
            ->join('purchases', 'purchase_details.purchase_id', '=', 'purchases.id')
            ->whereBetween('purchases.purchase_date', [$validated['start_date'], $validated['end_date']])
            ->where('purchases.purchase_status', '1')
            ->select(
                'purchases.purchase_no', 'purchases.purchase_date', 'purchases.supplier_id',
                'products.code', 'products.name',
                'purchase_details.quantity', 'purchase_details.unitcost', 'purchase_details.total'
            )
            ->get();

        $purchase_array[] = [
            'Date', 'No Purchase', 'Supplier', 'Product Code',
            'Product', 'Quantity', 'Unitcost', 'Total',
        ];

        foreach ($purchases as $purchase) {
            $purchase_array[] = [
                'Date' => $purchase->purchase_date,
                'No Purchase' => $purchase->purchase_no,
                'Supplier' => $purchase->supplier_id,
                'Product Code' => $purchase->product_code,
                'Product' => $purchase->product_name,
                'Quantity' => $purchase->quantity,
                'Unitcost' => $purchase->unitcost,
                'Total' => $purchase->total,
            ];
        }

        $this->exportExcel($purchase_array);
    }

    public function exportExcel(array $products): void
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '4000M');

        try {
            $spreadSheet = new Spreadsheet;
            $spreadSheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(20);
            $spreadSheet->getActiveSheet()->fromArray($products);
            $writer = new Xls($spreadSheet);
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="purchase-report.xls"');
            header('Cache-Control: max-age=0');
            ob_end_clean();
            $writer->save('php://output');
            exit();
        } catch (Exception) {
            return;
        }
    }
}
