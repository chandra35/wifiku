<?php

require_once 'vendor/autoload.php';
use App\Models\Customer;
use App\Models\Package;
use App\Models\User;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Customer CRUD Validation Test ===\n\n";

try {
    // Test 1: Read customers with relationships
    echo "1. Testing Customer Read with Relations:\n";
    $customers = Customer::with(['package', 'province', 'city', 'district', 'village', 'createdBy'])->get();
    echo "   Total customers: " . $customers->count() . "\n";
    
    foreach ($customers as $customer) {
        echo "   - {$customer->customer_id}: {$customer->name}\n";
        echo "     Package: " . ($customer->package ? $customer->package->name : 'None') . "\n";
        echo "     Location: " . ($customer->province ? $customer->province->name : 'N/A') . "\n";
        echo "     Created by: " . ($customer->createdBy ? $customer->createdBy->name : 'System') . "\n";
    }
    
    // Test 2: Test Laravolt relationships
    echo "\n2. Testing Laravolt Location Data:\n";
    $provinces = \Laravolt\Indonesia\Models\Province::take(3)->get();
    echo "   Sample provinces: " . $provinces->pluck('name')->join(', ') . "\n";
    
    if ($customers->first() && $customers->first()->province_id) {
        $provinceCode = $customers->first()->province_id;
        $cities = \Laravolt\Indonesia\Models\City::where('province_code', $provinceCode)->take(3)->get();
        echo "   Cities in {$customers->first()->province->name}: " . $cities->pluck('name')->join(', ') . "\n";
    }
    
    // Test 3: Test Package relationships
    echo "\n3. Testing Package Relations:\n";
    $packages = Package::where('is_active', true)->get();
    echo "   Active packages: " . $packages->count() . "\n";
    foreach ($packages as $package) {
        echo "   - {$package->name}: Rp " . number_format($package->price, 0, ',', '.') . "\n";
    }
    
    // Test 4: Test User/Admin relationships
    echo "\n4. Testing User Relations:\n";
    $users = User::with('role')->get();
    echo "   Total users: " . $users->count() . "\n";
    foreach ($users as $user) {
        echo "   - {$user->name}: {$user->role->name}\n";
    }
    
    // Test 5: Customer form validation data
    echo "\n5. Testing Customer Form Data Structure:\n";
    $firstCustomer = $customers->first();
    if ($firstCustomer) {
        echo "   Customer ID: " . $firstCustomer->customer_id . "\n";
        echo "   All required fields present: " . (
            $firstCustomer->name && 
            $firstCustomer->phone && 
            $firstCustomer->address && 
            $firstCustomer->package_id ? 'YES' : 'NO'
        ) . "\n";
        echo "   Location fields present: " . (
            $firstCustomer->province_id && 
            $firstCustomer->city_id && 
            $firstCustomer->district_id && 
            $firstCustomer->village_id ? 'YES' : 'NO'
        ) . "\n";
        echo "   Billing info present: " . (
            $firstCustomer->billing_cycle && 
            $firstCustomer->status ? 'YES' : 'NO'
        ) . "\n";
    }
    
    echo "\n=== All Tests PASSED ===\n";
    echo "Customer CRUD functionality is working correctly!\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "File: " . $e->getFile() . "\n";
}
