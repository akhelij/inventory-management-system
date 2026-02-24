<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProductImportController extends Controller
{
    public function create(): View
    {
        return view('products.import');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xls,xlsx',
        ]);

        try {
            $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rowRange = range(2, $sheet->getHighestDataRow());

            $data = [];
            foreach ($rowRange as $row) {
                $data[] = [
                    'name' => $sheet->getCell('A'.$row)->getValue(),
                    'slug' => $sheet->getCell('B'.$row)->getValue(),
                    'category_id' => $sheet->getCell('C'.$row)->getValue(),
                    'unit_id' => $sheet->getCell('D'.$row)->getValue(),
                    'code' => $sheet->getCell('E'.$row)->getValue(),
                    'quantity' => $sheet->getCell('F'.$row)->getValue(),
                    'quantity_alert' => $sheet->getCell('G'.$row)->getValue(),
                    'buying_price' => $sheet->getCell('H'.$row)->getValue(),
                    'selling_price' => $sheet->getCell('I'.$row)->getValue(),
                    'product_image' => $sheet->getCell('J'.$row)->getValue(),
                    'notes' => $sheet->getCell('K'.$row)->getValue(),
                ];
            }

            foreach ($data as $product) {
                Product::firstOrCreate([
                    'slug' => $product['slug'],
                    'code' => $product['code'],
                ], $product);
            }
        } catch (Exception $e) {
            throw $e;
        }

        return to_route('products.index')->with('success', 'Data product has been imported!');
    }
}
