<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

use App\Models\Customer;
use Carbon\Carbon;

echo "=== TESTING DATE DISPLAY IN ALL CUSTOMER VIEWS ===\n\n";

// Test Carbon date formatting
$testDate = Carbon::parse('2025-08-11');
echo "Test Carbon date (2025-08-11):\n";
echo "- format('d/m/Y'): " . $testDate->format('d/m/Y') . "\n";
echo "- format('Y-m-d'): " . $testDate->format('Y-m-d') . "\n";
echo "- format('m/d/Y'): " . $testDate->format('m/d/Y') . "\n\n";

// Get a customer with dates
$customer = Customer::first();
if ($customer) {
    echo "Customer: {$customer->name}\n";
    echo "Raw installation_date: " . ($customer->installation_date ?? 'null') . "\n";
    echo "Raw next_billing_date: " . ($customer->next_billing_date ?? 'null') . "\n";
    echo "Raw created_at: " . ($customer->created_at ?? 'null') . "\n\n";
    
    // Test all the format combinations used in views
    echo "=== FORMAT TESTING ===\n";
    
    if ($customer->installation_date) {
        echo "Installation Date:\n";
        if (is_string($customer->installation_date)) {
            echo "- Raw string: " . $customer->installation_date . "\n";
        } else {
            echo "- Carbon object format('d/m/Y'): " . $customer->installation_date->format('d/m/Y') . "\n";
            echo "- Carbon object format('Y-m-d'): " . $customer->installation_date->format('Y-m-d') . "\n";
            echo "- Carbon object format('m/d/Y'): " . $customer->installation_date->format('m/d/Y') . "\n";
        }
        echo "\n";
    }
    
    if ($customer->next_billing_date) {
        echo "Next Billing Date:\n";
        if (is_string($customer->next_billing_date)) {
            echo "- Raw string: " . $customer->next_billing_date . "\n";
        } else {
            echo "- Carbon object format('d/m/Y'): " . $customer->next_billing_date->format('d/m/Y') . "\n";
            echo "- Carbon object format('Y-m-d'): " . $customer->next_billing_date->format('Y-m-d') . "\n";
            echo "- Carbon object format('m/d/Y'): " . $customer->next_billing_date->format('m/d/Y') . "\n";
        }
        echo "\n";
    }
    
    if ($customer->created_at) {
        echo "Created At:\n";
        echo "- Carbon object format('d/m/Y'): " . $customer->created_at->format('d/m/Y') . "\n";
        echo "- Carbon object format('Y-m-d'): " . $customer->created_at->format('Y-m-d') . "\n";
        echo "- Carbon object format('m/d/Y'): " . $customer->created_at->format('m/d/Y') . "\n";
        echo "\n";
    }
    
    // Test the exact code used in views
    echo "=== EXACT VIEW CODE TESTING ===\n";
    
    echo "show.blade.php installation_date display:\n";
    $showInstallation = $customer->installation_date ? (is_string($customer->installation_date) ? $customer->installation_date : $customer->installation_date->format('d/m/Y')) : '-';
    echo "Result: " . $showInstallation . "\n\n";
    
    echo "show.blade.php next_billing_date display:\n";
    $showBilling = $customer->next_billing_date ? (is_string($customer->next_billing_date) ? $customer->next_billing_date : $customer->next_billing_date->format('d/m/Y')) : '-';
    echo "Result: " . $showBilling . "\n\n";
    
    echo "index.blade.php created_at display:\n";
    $indexCreated = $customer->created_at->format('d/m/Y');
    echo "Result: " . $indexCreated . "\n\n";
    
    echo "edit.blade.php installation_date input value:\n";
    $editInstallation = old('installation_date', $customer->installation_date ? (is_string($customer->installation_date) ? $customer->installation_date : $customer->installation_date->format('Y-m-d')) : '');
    echo "Result: " . $editInstallation . "\n\n";
    
    echo "edit.blade.php next_billing_date input value:\n";
    $editBilling = old('next_billing_date', $customer->next_billing_date ? (is_string($customer->next_billing_date) ? $customer->next_billing_date : $customer->next_billing_date->format('Y-m-d')) : '');
    echo "Result: " . $editBilling . "\n\n";
}

echo "=== TESTING BROWSER LOCALE ===\n";
echo "PHP Locale: " . setlocale(LC_TIME, 0) . "\n";
echo "Carbon Locale: " . Carbon::getLocale() . "\n";

// Test different Carbon locales
Carbon::setLocale('id');
$testDate = Carbon::parse('2025-08-11');
echo "Indonesian locale format: " . $testDate->format('d/m/Y') . "\n";

Carbon::setLocale('en');
$testDate = Carbon::parse('2025-08-11');
echo "English locale format: " . $testDate->format('d/m/Y') . "\n";

echo "\n=== CONCLUSION ===\n";
echo "All views should show Indonesian format (dd/mm/yyyy)\n";
echo "If you still see mm/dd/yyyy, it might be:\n";
echo "1. Browser cache - try Ctrl+F5 to hard refresh\n";
echo "2. Browser locale settings affecting date input display\n";
echo "3. Different page/view than the ones checked\n";
echo "4. JavaScript date formatting overriding server output\n";
