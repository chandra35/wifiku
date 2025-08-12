<?php
use Illuminate\Support\Facades\Route;
use App\Models\Customer;
use App\Models\Payment;

Route::get('/api/customer/{customer}/unpaid-payments', function (Customer $customer) {
    $now = now();
    $payments = Payment::where('customer_id', $customer->id)
        ->where('status', 'pending')
        ->where(function($q) use ($now) {
            $q->whereMonth('billing_date', $now->month)
              ->whereYear('billing_date', $now->year)
              ->orWhere('due_date', '<', $now->toDateString());
        })
        ->orderBy('billing_date', 'asc')
        ->get();
    $result = $payments->map(function($p) {
        return [
            'id' => $p->id,
            'billing_month' => $p->billing_date->format('F Y'),
            'amount_fmt' => $p->getFormattedAmount(),
            'is_overdue' => $p->isOverdue(),
            'status_label' => $p->getStatusLabel(),
        ];
    });
    return response()->json($result);
});
