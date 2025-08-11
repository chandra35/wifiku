<?php

require_once 'vendor/autoload.php';
use App\Models\Customer;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Customer Date Fields ===\n\n";

try {
    $customer = Customer::first();
    
    if ($customer) {
        echo "Customer: {$customer->name}\n";
        echo "Customer ID: {$customer->customer_id}\n\n";
        
        // Test all date fields
        echo "Date Field Tests:\n";
        
        echo "1. Birth Date:\n";
        echo "   Raw: " . ($customer->birth_date ?? 'NULL') . "\n";
        echo "   Type: " . gettype($customer->birth_date) . "\n";
        if ($customer->birth_date) {
            if (is_string($customer->birth_date)) {
                echo "   Formatted (string): " . $customer->birth_date . "\n";
            } else {
                echo "   Formatted (Carbon): " . $customer->birth_date->format('d/m/Y') . "\n";
            }
        }
        
        echo "\n2. Installation Date:\n";
        echo "   Raw: " . ($customer->installation_date ?? 'NULL') . "\n";
        echo "   Type: " . gettype($customer->installation_date) . "\n";
        if ($customer->installation_date) {
            if (is_string($customer->installation_date)) {
                echo "   Formatted (string): " . $customer->installation_date . "\n";
            } else {
                echo "   Formatted (Carbon): " . $customer->installation_date->format('d/m/Y') . "\n";
            }
        }
        
        echo "\n3. Next Billing Date:\n";
        echo "   Raw: " . ($customer->next_billing_date ?? 'NULL') . "\n";
        echo "   Type: " . gettype($customer->next_billing_date) . "\n";
        if ($customer->next_billing_date) {
            if (is_string($customer->next_billing_date)) {
                echo "   Formatted (string): " . $customer->next_billing_date . "\n";
            } else {
                echo "   Formatted (Carbon): " . $customer->next_billing_date->format('d/m/Y') . "\n";
            }
        }
        
        echo "\n4. Created At:\n";
        echo "   Raw: " . $customer->created_at . "\n";
        echo "   Type: " . gettype($customer->created_at) . "\n";
        echo "   Formatted: " . $customer->created_at->format('d/m/Y H:i:s') . "\n";
        
        echo "\n=== All Date Fields Working ===\n";
        
    } else {
        echo "No customers found.\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "File: " . $e->getFile() . "\n";
}
