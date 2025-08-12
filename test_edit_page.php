<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

use App\Models\Customer;

echo "=== TESTING EDIT PELANGGAN PAGE ===\n\n";

$customer = Customer::first();
if ($customer) {
    echo "Customer: {$customer->name}\n";
    echo "ID: {$customer->id}\n\n";
    
    echo "=== RAW DATABASE VALUES ===\n";
    echo "installation_date (raw): " . ($customer->getRawOriginal('installation_date') ?? 'null') . "\n";
    echo "next_billing_date (raw): " . ($customer->getRawOriginal('next_billing_date') ?? 'null') . "\n";
    echo "birth_date (raw): " . ($customer->getRawOriginal('birth_date') ?? 'null') . "\n\n";
    
    echo "=== CARBON OBJECTS ===\n";
    echo "installation_date (Carbon): " . ($customer->installation_date ?? 'null') . "\n";
    echo "next_billing_date (Carbon): " . ($customer->next_billing_date ?? 'null') . "\n";
    echo "birth_date (Carbon): " . ($customer->birth_date ?? 'null') . "\n\n";
    
    echo "=== EXACT EDIT FORM VALUES ===\n";
    
    // Test installation_date edit form value
    $installationFormValue = old('installation_date', $customer->installation_date ? (is_string($customer->installation_date) ? $customer->installation_date : $customer->installation_date->format('Y-m-d')) : '');
    echo "Installation Date form value: " . $installationFormValue . "\n";
    
    // Test next_billing_date edit form value  
    $billingFormValue = old('next_billing_date', $customer->next_billing_date ? (is_string($customer->next_billing_date) ? $customer->next_billing_date : $customer->next_billing_date->format('Y-m-d')) : '');
    echo "Next Billing Date form value: " . $billingFormValue . "\n";
    
    // Test birth_date edit form value
    $birthFormValue = old('birth_date', $customer->birth_date ? (is_string($customer->birth_date) ? $customer->birth_date : $customer->birth_date->format('Y-m-d')) : '');
    echo "Birth Date form value: " . $birthFormValue . "\n\n";
    
    echo "=== DATE INPUT ANALYSIS ===\n";
    echo "HTML5 date input HARUS menggunakan format Y-m-d (YYYY-MM-DD)\n";
    echo "Browser akan menampilkan sesuai locale sistem operasi\n\n";
    
    if ($customer->installation_date) {
        echo "Installation Date Analysis:\n";
        echo "- Database: " . $customer->getRawOriginal('installation_date') . "\n";
        echo "- Carbon: " . $customer->installation_date . "\n";
        echo "- Display format (d/m/Y): " . $customer->installation_date->format('d/m/Y') . "\n";
        echo "- HTML input format (Y-m-d): " . $customer->installation_date->format('Y-m-d') . "\n";
        echo "- US format (m/d/Y): " . $customer->installation_date->format('m/d/Y') . "\n\n";
    }
    
    if ($customer->next_billing_date) {
        echo "Next Billing Date Analysis:\n";
        echo "- Database: " . $customer->getRawOriginal('next_billing_date') . "\n";
        echo "- Carbon: " . $customer->next_billing_date . "\n";
        echo "- Display format (d/m/Y): " . $customer->next_billing_date->format('d/m/Y') . "\n";
        echo "- HTML input format (Y-m-d): " . $customer->next_billing_date->format('Y-m-d') . "\n";
        echo "- US format (m/d/Y): " . $customer->next_billing_date->format('m/d/Y') . "\n\n";
    }
}

echo "=== BROWSER BEHAVIOR EXPLANATION ===\n";
echo "1. HTML5 input type='date' REQUIRES Y-m-d format in value attribute\n";
echo "2. Browser displays date according to user's locale/language settings\n";
echo "3. If your Windows is set to English (US), browser shows mm/dd/yyyy\n";
echo "4. If your Windows is set to Indonesian, browser shows dd/mm/yyyy\n";
echo "5. This is browser behavior, NOT application error\n\n";

echo "=== CHECKING WINDOWS LOCALE ===\n";
// Check system locale
$locale = setlocale(LC_TIME, 0);
echo "Current PHP locale: " . $locale . "\n";

// Check if running on Windows and get system locale
if (PHP_OS_FAMILY === 'Windows') {
    $windowsLocale = exec('powershell -Command "Get-Culture | Select-Object -ExpandProperty Name"');
    echo "Windows locale: " . $windowsLocale . "\n";
}

echo "\n=== SOLUSI ===\n";
echo "1. Jika ingin mengubah tampilan browser date input:\n";
echo "   - Ubah language/locale Windows ke Indonesian\n";
echo "   - Atau ubah browser language settings\n";
echo "2. Atau gunakan date picker JavaScript dengan format Indonesia\n";
echo "3. NAMUN: Aplikasi sudah BENAR menggunakan standar HTML5!\n";
