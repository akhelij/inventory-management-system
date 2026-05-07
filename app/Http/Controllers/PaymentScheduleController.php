<?php

namespace App\Http\Controllers;

use App\Exports\PaymentScheduleExport;
use App\Models\Customer;
use App\Models\InstallmentEntry;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentScheduleController extends Controller
{
    public function store(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'total_installments' => 'required|integer|min:2|max:24',
            'period_days' => 'required|integer|min:7|max:365',
        ]);

        DB::transaction(function () use ($request, $order) {
            $schedule = PaymentSchedule::create([
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'total_installments' => $request->total_installments,
                'period_days' => $request->period_days,
                'total_amount' => $order->total,
                'user_id' => Auth::id(),
            ]);

            $baseAmount = floor($order->total / $request->total_installments * 100) / 100;
            $remainder = $order->total - ($baseAmount * ($request->total_installments - 1));

            for ($i = 1; $i <= $request->total_installments; $i++) {
                InstallmentEntry::create([
                    'payment_schedule_id' => $schedule->id,
                    'installment_number' => $i,
                    'amount' => $i === $request->total_installments ? $remainder : $baseAmount,
                    'due_date' => now()->addDays($request->period_days * $i),
                    'status' => 'pending',
                ]);
            }
        });

        return back()->with('success', 'Payment schedule created successfully.');
    }

    public function markPaid(Request $request, InstallmentEntry $entry): RedirectResponse
    {
        $remaining = (float) $entry->amount - (float) $entry->paid_amount;

        if ($remaining <= 0) {
            return back()->with('error', 'This installment is already fully paid.');
        }

        $request->validate([
            'paid_date' => 'required|string',
            'amount' => "required|numeric|min:0.01|max:{$remaining}",
            'payment_type' => 'required|in:HandCash,Cheque,Exchange',
        ]);

        $paidDate = \Carbon\Carbon::createFromFormat('d/m/Y', $request->paid_date);
        $schedule = $entry->schedule;
        $amount = (float) $request->amount;

        DB::transaction(function () use ($entry, $schedule, $paidDate, $amount, $request) {
            $payment = Payment::create([
                'customer_id' => $schedule->customer_id,
                'date' => $paidDate->format('Y-m-d'),
                'nature' => 'INST-'.$schedule->order->invoice_no.'-'.$entry->installment_number,
                'payment_type' => $request->payment_type,
                'echeance' => $paidDate->format('Y-m-d'),
                'amount' => $amount,
            ]);

            $newPaid = (float) $entry->paid_amount + $amount;
            $isFullyPaid = $newPaid >= (float) $entry->amount;

            $entry->update([
                'paid_amount' => $newPaid,
                'status' => $isFullyPaid ? 'paid' : 'partial',
                'paid_at' => $isFullyPaid ? $paidDate : $entry->paid_at,
                'payment_id' => $payment->id,
            ]);
        });

        return back()->with('success', 'Payment recorded.');
    }

    public function export(Request $request, Customer $customer): void
    {
        $request->validate([
            'schedule_ids' => 'required|array',
            'schedule_ids.*' => 'integer|exists:payment_schedules,id',
        ]);

        (new PaymentScheduleExport($customer, $request->schedule_ids))->export();
    }
}
