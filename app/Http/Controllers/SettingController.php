<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppSetting;

class SettingController extends Controller
{
    public function index()
    {
        $prefix = AppSetting::getValue('customer_prefix', 'CUS');
        $ppn = AppSetting::getValue('ppn', '0');
        $invoice = AppSetting::getValue('invoice_note', '');
        return view('settings.index', compact('prefix', 'ppn', 'invoice'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'customer_prefix' => 'required|string|max:10',
            'ppn' => 'required|numeric|min:0|max:100',
            'invoice_note' => 'nullable|string|max:255',
        ]);
        AppSetting::setValue('customer_prefix', $request->customer_prefix);
        AppSetting::setValue('ppn', $request->ppn);
        AppSetting::setValue('invoice_note', $request->invoice_note);
        return redirect()->route('settings.index')->with('success', 'Pengaturan berhasil disimpan.');
    }
}
