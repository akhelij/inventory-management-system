<?php

namespace App\Exports;

use App\Models\Customer;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PendingPaymentsExport
{
    public function __construct(
        protected Customer $customer,
    ) {}

    public function export(): void
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $this->setTitle($sheet);
        $this->setCustomerInfo($sheet);
        $this->setHeaders($sheet);

        $row = 8;
        $totalPending = 0;

        $pendingPayments = $this->customer->payments()
            ->where('cashed_in', false)
            ->where('reported', false)
            ->orderBy('echeance', 'asc')
            ->get();

        foreach ($pendingPayments as $payment) {
            $sheet->setCellValue("A{$row}", $payment->date);
            $sheet->setCellValue("B{$row}", $payment->nature);
            $sheet->setCellValue("C{$row}", $payment->payment_type);
            $sheet->setCellValue("D{$row}", number_format($payment->amount, 2, ',', ' '));
            $sheet->setCellValue("E{$row}", $payment->echeance);
            $sheet->setCellValue("F{$row}", 'En attente');
            $sheet->setCellValue("G{$row}", $payment->description ?? '');

            $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $totalPending += $payment->amount;
            $row++;
        }

        $sheet->setCellValue("C{$row}", 'TOTAL:');
        $sheet->setCellValue("D{$row}", number_format($totalPending, 2, ',', ' ').' MAD');
        $sheet->getStyle("C{$row}:D{$row}")->getFont()->setBold(true);
        $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $sheet->getStyle("C{$row}:D{$row}")->applyFromArray([
            'borders' => ['top' => ['borderStyle' => Border::BORDER_DOUBLE]],
        ]);

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'paiements_en_attente_'.str_replace(' ', '_', $this->customer->name).'_'.date('Y-m-d').'.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$fileName.'"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    private function setTitle($sheet): void
    {
        $sheet->setCellValue('A1', 'PLATINIUM ELECTRO - Paiements en attente');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    private function setCustomerInfo($sheet): void
    {
        $sheet->setCellValue('A3', 'Client:');
        $sheet->setCellValue('B3', $this->customer->name);
        $sheet->setCellValue('A4', 'Email:');
        $sheet->setCellValue('B4', $this->customer->email);
        $sheet->setCellValue('A5', 'Telephone:');
        $sheet->setCellValue('B5', $this->customer->phone);
        $sheet->setCellValue('D3', "Date d'export:");
        $sheet->setCellValue('E3', now()->format('Y-m-d H:i:s'));
    }

    private function setHeaders($sheet): void
    {
        $headers = ['Date', 'Nature', 'Type', 'Montant (MAD)', 'Echeance', 'Statut', 'Description'];
        $col = 'A';

        foreach ($headers as $header) {
            $sheet->setCellValue($col.'7', $header);
            $col++;
        }

        $sheet->getStyle('A7:G7')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'cc0000']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);
    }
}
