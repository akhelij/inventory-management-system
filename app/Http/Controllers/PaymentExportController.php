<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

class PaymentExportController extends Controller
{
    public function create(Request $request): void
    {
        $customers = Customer::where('user_id', Auth::id())
            ->with('orders', 'payments')
            ->search($request->search)
            ->get();

        if ($request->input('only_unpaid')) {
            $customers = $customers->where('is_out_of_limit', true);
        }

        $payment_array[] = [
            'Date', 'Client', 'Nature', 'Ville', 'Banque', 'Echeance', 'Montant', 'Reportes',
        ];

        foreach ($customers as $customer) {
            foreach ($customer->payments as $payment) {
                $payment_array[] = [
                    'Date' => Carbon::parse($payment->date)->format('d/m/Y'),
                    'Client' => $customer->name,
                    'Nature' => $payment->nature,
                    'Ville' => $customer->city,
                    'Banque' => $payment->bank,
                    'Echeance' => Carbon::parse($payment->echeance)->format('d/m/Y'),
                    'Montant' => $payment->reported ? '' : $payment->amount,
                    'Reportes' => $payment->reported ? $payment->amount : '',
                ];
            }
        }

        $this->store($payment_array);
    }

    public function store(array $payments): void
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '4000M');

        try {
            $spreadSheet = new Spreadsheet;
            $spreadSheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(20);
            $spreadSheet->getActiveSheet()->fromArray($payments);
            $writer = new Xls($spreadSheet);
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="payments.xls"');
            header('Cache-Control: max-age=0');
            ob_end_clean();
            $writer->save('php://output');
            exit();
        } catch (Exception) {
            return;
        }
    }
}
