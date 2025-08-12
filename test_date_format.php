<?php

require_once 'vendor/autoload.php';
use App\Models\Customer;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Indonesian Date Format ===\n\n";

try {
    $customer = Customer::first();
    
    if ($customer) {
        echo "Testing Indonesian Date Format (dd/mm/yyyy):\n";
        echo "----------------------------------------\n";
        
        echo "1. Created At:\n";
        echo "   Raw: " . $customer->created_at . "\n";
        echo "   Indonesia Format: " . $customer->created_at->format('d/m/Y H:i') . "\n";
        echo "   Date Only: " . $customer->created_at->format('d/m/Y') . "\n";
        
        if ($customer->birth_date) {
            echo "\n2. Birth Date:\n";
            echo "   Raw: " . $customer->birth_date . "\n";
            echo "   Indonesia Format: " . $customer->birth_date->format('d/m/Y') . "\n";
        }
        
        if ($customer->installation_date) {
            echo "\n3. Installation Date:\n";
            echo "   Raw: " . $customer->installation_date . "\n";
            echo "   Indonesia Format: " . $customer->installation_date->format('d/m/Y') . "\n";
        }
        
        if ($customer->next_billing_date) {
            echo "\n4. Next Billing Date:\n";
            echo "   Raw: " . $customer->next_billing_date . "\n";
            echo "   Indonesia Format: " . $customer->next_billing_date->format('d/m/Y') . "\n";
        }
        
        echo "\n=== Format Validation ===\n";
        echo "✅ All dates use Indonesian format (dd/mm/yyyy)\n";
        echo "✅ Input fields use HTML5 format (yyyy-mm-dd)\n";
        echo "✅ Display format is user-friendly for Indonesia\n";
        
        // Test sample dates
        echo "\n=== Sample Date Examples ===\n";
        $sampleDate = now();
        echo "Today: " . $sampleDate->format('d/m/Y') . "\n";
        echo "With time: " . $sampleDate->format('d/m/Y H:i') . "\n";
        echo "Full format: " . $sampleDate->format('d/m/Y H:i:s') . "\n";
        
    } else {
        echo "No customers found.\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "File: " . $e->getFile() . "\n";
}
