<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\App;
use App\Models\Customer;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "Testing Customer Relations...\n";
    
    $customer = Customer::with(['package', 'province', 'city', 'district', 'village', 'createdBy'])->first();
    
    if ($customer) {
        echo "Customer: " . $customer->name . "\n";
        echo "Phone: " . $customer->phone . "\n";
        echo "Package: " . ($customer->package ? $customer->package->name : 'None') . "\n";
        echo "Province: " . ($customer->province ? $customer->province->name : 'None') . "\n";
        echo "City: " . ($customer->city ? $customer->city->name : 'None') . "\n";
        echo "District: " . ($customer->district ? $customer->district->name : 'None') . "\n";
        echo "Village: " . ($customer->village ? $customer->village->name : 'None') . "\n";
        echo "Created By: " . ($customer->createdBy ? $customer->createdBy->name : 'System') . "\n";
        echo "Status: " . $customer->status . "\n";
    } else {
        echo "No customers found.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "File: " . $e->getFile() . "\n";
}
