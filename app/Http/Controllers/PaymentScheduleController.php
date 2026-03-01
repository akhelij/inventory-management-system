<?php

namespace App\Http\Controllers;

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
        $schedule = $entry->schedule;

        DB::transaction(function () use ($entry, $schedule) {
            $payment = Payment::create([
                'customer_id' => $schedule->customer_id,
                'date' => now()->format('Y-m-d'),
                'nature' => 'INST-'.$schedule->order->invoice_no.'-'.$entry->installment_number,
                'payment_type' => 'HandCash',
                'echeance' => now()->format('Y-m-d'),
                'amount' => $entry->amount,
            ]);

            $entry->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_id' => $payment->id,
            ]);
        });

        return back()->with('success', 'Installment marked as paid.');
    }
}
