<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

class ProductExportController extends Controller
{
    public function create(): void
    {
        $products = Product::where('quantity', '>', 0)->orderBy('name')->get();

        $product_array[] = [
            'Product Name', 'Category Name', 'Product Code', 'Stock',
            'Buying Price', 'Selling Price', 'Note',
        ];

        foreach ($products as $product) {
            $product_array[] = [
                'Product Name' => $product->name,
                'Category Name' => $product->category?->name,
                'Product Code' => $product->code,
                'Stock' => $product->quantity,
                'Buying Price' => $product->buying_price,
                'Selling Price' => $product->selling_price,
                'Note' => $product->note,
            ];
        }

        if (! auth()->user()->hasRole('admin')) {
            foreach ($product_array as $key => $product) {
                unset($product_array[$key]['Buying Price']);
            }
        }

        $this->store($product_array);
    }

    public function store(array $products): void
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '4000M');

        try {
            $spreadSheet = new Spreadsheet;
            $spreadSheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(20);
            $spreadSheet->getActiveSheet()->fromArray($products);
            $writer = new Xls($spreadSheet);
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="products.xls"');
            header('Cache-Control: max-age=0');
            ob_end_clean();
            $writer->save('php://output');
            exit();
        } catch (Exception) {
            return;
        }
    }
}
