<?php

use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/customer/{customer}/unpaid-payments', function (Customer $customer) {
    $now = now();
    $payments = Payment::where('customer_id', $customer->id)
        ->whereIn('status', ['pending', 'overdue'])
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
