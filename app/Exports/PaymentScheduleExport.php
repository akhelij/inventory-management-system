<?php

namespace App\Exports;

use App\Models\Customer;
use App\Models\PaymentSchedule;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PaymentScheduleExport
{
    public function __construct(
        protected Customer $customer,
        protected array $scheduleIds,
    ) {}

    public function export(): void
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'PLATINIUM ELECTRO - Echeancier de paiement');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A3', 'Client:');
        $sheet->setCellValue('B3', $this->customer->name);
        $sheet->setCellValue('A4', 'Telephone:');
        $sheet->setCellValue('B4', $this->customer->phone);
        $sheet->setCellValue('D3', "Date d'export:");
        $sheet->setCellValue('E3', now()->format('d/m/Y H:i'));

        $row = 6;

        $schedules = PaymentSchedule::with(['order', 'entries.payment', 'advancePayment'])
            ->whereIn('id', $this->scheduleIds)
            ->where('customer_id', $this->customer->id)
            ->get();

        foreach ($schedules as $schedule) {
            $sheet->setCellValue("A{$row}", 'Commande: '.$schedule->order->invoice_no);
            $sheet->setCellValue("D{$row}", $schedule->total_installments.'x chaque '.$schedule->period_days.' jours');
            $sheet->setCellValue("F{$row}", number_format($schedule->total_amount, 2, ',', ' ').' MAD');
            $sheet->getStyle("A{$row}:F{$row}")->getFont()->setBold(true)->setSize(11);
            $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'e8f4fd']],
            ]);
            $row++;

            $headers = ['#', 'Montant (MAD)', 'Date Echeance', 'Statut', 'Date Paiement', 'Nature'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col.$row, $header);
                $col++;
            }
            $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '206bc4']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
            $row++;

            if ($schedule->advance_amount > 0) {
                $sheet->setCellValue("A{$row}", 'Avance');
                $sheet->setCellValue("B{$row}", number_format($schedule->advance_amount, 2, ',', ' '));
                $sheet->setCellValue("C{$row}", $schedule->advancePayment?->date ?? '-');
                $sheet->setCellValue("D{$row}", $schedule->advancePayment?->cashed_in ? 'Encaissé' : 'En attente');
                $sheet->setCellValue("E{$row}", $schedule->advancePayment?->cashed_in_at?->format('d/m/Y') ?? '-');
                $sheet->setCellValue("F{$row}", $schedule->advancePayment?->nature ?? 'ADV-'.$schedule->order->invoice_no);
                $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'fff3cd']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                $row++;
            }

            foreach ($schedule->entries as $entry) {
                $sheet->setCellValue("A{$row}", $entry->installment_number);
                $sheet->setCellValue("B{$row}", number_format($entry->amount, 2, ',', ' '));
                $sheet->setCellValue("C{$row}", $entry->due_date->format('d/m/Y'));
                $sheet->setCellValue("D{$row}", match ($entry->status) {
                    'paid' => 'Payé',
                    'overdue' => 'En retard',
                    default => 'En attente',
                });
                $sheet->setCellValue("E{$row}", $entry->paid_at?->format('d/m/Y') ?? '-');
                $sheet->setCellValue("F{$row}", $entry->payment?->nature ?? '-');

                $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                if ($entry->status === 'paid') {
                    $sheet->getStyle("D{$row}")->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF28a745'));
                } elseif ($entry->status === 'overdue') {
                    $sheet->getStyle("D{$row}")->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFdc3545'));
                }

                $row++;
            }

            $row++;
        }

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'echeancier_'.str_replace(' ', '_', $this->customer->name).'_'.date('Y-m-d').'.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$fileName.'"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}
