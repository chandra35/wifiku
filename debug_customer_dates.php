<?php

require_once 'vendor/autoload.php';
use App\Models\Customer;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Checking Customer Date Data ===\n\n";

try {
    $customers = Customer::all();
    
    foreach ($customers as $customer) {
        echo "Customer: {$customer->name} ({$customer->customer_id})\n";
        echo "----------------------------------------\n";
        
        echo "Installation Date:\n";
        echo "  Raw value: " . ($customer->installation_date ?? 'NULL') . "\n";
        echo "  Type: " . gettype($customer->installation_date) . "\n";
        if ($customer->installation_date) {
            if (is_string($customer->installation_date)) {
                echo "  Is string - Value: " . $customer->installation_date . "\n";
                // Try to parse as date
                try {
                    $date = \Carbon\Carbon::parse($customer->installation_date);
                    echo "  Parsed as Carbon: " . $date->format('d/m/Y') . "\n";
                } catch (Exception $e) {
                    echo "  Cannot parse as date: " . $e->getMessage() . "\n";
                }
            } else {
                echo "  Is Carbon object: " . $customer->installation_date->format('d/m/Y') . "\n";
            }
        }
        
        echo "\nNext Billing Date:\n";
        echo "  Raw value: " . ($customer->next_billing_date ?? 'NULL') . "\n";
        echo "  Type: " . gettype($customer->next_billing_date) . "\n";
        if ($customer->next_billing_date) {
            if (is_string($customer->next_billing_date)) {
                echo "  Is string - Value: " . $customer->next_billing_date . "\n";
                // Try to parse as date
                try {
                    $date = \Carbon\Carbon::parse($customer->next_billing_date);
                    echo "  Parsed as Carbon: " . $date->format('d/m/Y') . "\n";
                } catch (Exception $e) {
                    echo "  Cannot parse as date: " . $e->getMessage() . "\n";
                }
            } else {
                echo "  Is Carbon object: " . $customer->next_billing_date->format('d/m/Y') . "\n";
            }
        }
        
        echo "\n" . str_repeat("=", 50) . "\n\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "File: " . $e->getFile() . "\n";
}
