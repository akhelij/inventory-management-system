<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use App\Models\Customer;

class PendingPaymentsExport
{
    protected $customer;

    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }

    public function export()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setCellValue('A1', 'PLATINIUM ELECTRO - Paiements en attente');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Customer info
        $sheet->setCellValue('A3', 'Client:');
        $sheet->setCellValue('B3', $this->customer->name);
        $sheet->setCellValue('A4', 'Email:');
        $sheet->setCellValue('B4', $this->customer->email);
        $sheet->setCellValue('A5', 'Téléphone:');
        $sheet->setCellValue('B5', $this->customer->phone);
        
        $sheet->setCellValue('D3', 'Date d\'export:');
        $sheet->setCellValue('E3', date('Y-m-d H:i:s'));
        
        // Headers
        $headers = ['Date', 'Nature', 'Type', 'Montant (MAD)', 'Échéance', 'Statut', 'Description'];
        $col = 'A';
        $row = 7;
        
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $col++;
        }

        // Style headers
        $headerRange = 'A7:G7';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'cc0000'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Add data
        $row = 8;
        $totalPending = 0;
        
        // Get only pending payments
        $pendingPayments = $this->customer->payments()
            ->where('cashed_in', false)
            ->where('reported', false)
            ->orderBy('echeance', 'asc')
            ->get();

        foreach ($pendingPayments as $payment) {
            $sheet->setCellValue('A' . $row, $payment->date);
            $sheet->setCellValue('B' . $row, $payment->nature);
            $sheet->setCellValue('C' . $row, $payment->payment_type);
            $sheet->setCellValue('D' . $row, number_format($payment->amount, 2, ',', ' '));
            $sheet->setCellValue('E' . $row, $payment->echeance);
            $sheet->setCellValue('F' . $row, 'En attente');
            $sheet->setCellValue('G' . $row, $payment->description ?? '');
            
            // Apply borders
            $sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ]);
            
            // Center align certain columns
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            // Right align amount
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            
            $totalPending += $payment->amount;
            $row++;
        }

        // Add total row
        $sheet->setCellValue('C' . $row, 'TOTAL:');
        $sheet->setCellValue('D' . $row, number_format($totalPending, 2, ',', ' ') . ' MAD');
        $sheet->getStyle('C' . $row . ':D' . $row)->getFont()->setBold(true);
        $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        
        // Apply border to total row
        $sheet->getStyle('C' . $row . ':D' . $row)->applyFromArray([
            'borders' => [
                'top' => [
                    'borderStyle' => Border::BORDER_DOUBLE,
                ],
            ],
        ]);

        // Auto-size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Create writer and save
        $writer = new Xlsx($spreadsheet);
        
        // Set headers for download
        $fileName = 'paiements_en_attente_' . str_replace(' ', '_', $this->customer->name) . '_' . date('Y-m-d') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
}
