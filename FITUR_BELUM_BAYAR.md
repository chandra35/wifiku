# FITUR BELUM BAYAR - SISTEM PRA-BAYAR WIFIKU

## 🎯 OVERVIEW

Fitur "Belum Bayar" adalah sistem monitoring pembayaran untuk ISP dengan model **pra-bayar**. Sistem ini memungkinkan admin untuk memantau pelanggan yang belum membayar, otomatis suspend pelanggan yang terlambat, dan mengelola tagihan dengan mudah.

## 🏗️ SISTEM PRA-BAYAR

### Konsep Pra-Bayar:
1. **Pembayaran di Awal**: Pelanggan bayar sebelum menggunakan layanan
2. **Tagihan Saat Pasang**: Saat pemasangan (install_date), pelanggan harus bayar bulan pertama
3. **Tagihan Bulanan**: Setiap bulan pelanggan harus bayar sebelum tanggal jatuh tempo
4. **Auto Suspend**: Jika terlambat bayar >3 hari, otomatis di-suspend

### Flow Pembayaran:
```
Pasang Baru → Bayar Bulan 1 → Aktif → Tagihan Bulan 2 → Bayar → Aktif → dst...
```

## 📊 FITUR YANG TERSEDIA

### 1. Dashboard Statistik
- **Belum Bayar**: Jumlah tagihan pending yang belum jatuh tempo
- **Terlambat**: Jumlah tagihan yang sudah lewat jatuh tempo
- **Sudah Bayar**: Jumlah pembayaran bulan ini
- **Total Tunggakan**: Total amount yang belum dibayar

### 2. Filter Tabs
- **Belum Bayar**: Tagihan pending yang belum jatuh tempo
- **Terlambat**: Tagihan yang sudah lewat jatuh tempo (overdue)
- **Sudah Bayar**: Riwayat pembayaran yang sudah dikonfirmasi

### 3. Fitur Search
- Cari berdasarkan nama pelanggan
- Cari berdasarkan ID pelanggan
- Cari berdasarkan nomor invoice

### 4. Tabel Informasi Lengkap
- **Invoice Number**: Nomor tagihan otomatis (INV20250811XXXX)
- **Data Pelanggan**: Nama dan ID pelanggan
- **Paket**: Paket internet yang digunakan
- **Periode Tagihan**: Untuk bulan/periode mana
- **Jatuh Tempo**: Kapan harus dibayar
- **Amount**: Jumlah yang harus dibayar
- **Status**: Belum Bayar/Terlambat/Sudah Bayar
- **Hari Terlambat**: Berapa hari sudah terlambat

### 5. Action Buttons
- **👁️ Detail**: Lihat detail tagihan lengkap
- **✅ Bayar**: Konfirmasi pembayaran (manual)
- **👤 Pelanggan**: Lihat detail pelanggan

### 6. Auto Management
- **Auto Suspend**: Suspend pelanggan terlambat >3 hari
- **Auto Activate**: Aktifkan kembali setelah bayar
- **Auto Generate**: Buat tagihan bulan berikutnya otomatis

## 🔄 CARA KERJA SISTEM

### 1. Saat Customer Baru Daftar
```php
// Otomatis dibuat payment pertama
$payment = Payment::create([
    'customer_id' => $customer->id,
    'amount' => $customer->package->price,
    'billing_date' => $customer->installation_date,
    'due_date' => $customer->installation_date, // Pra-bayar
    'status' => 'pending'
]);
```

### 2. Saat Admin Konfirmasi Pembayaran
```php
// 1. Mark payment as paid
$payment->markAsPaid($admin->id);

// 2. Generate tagihan bulan berikutnya
$this->generateNextPayment($customer);

// 3. Aktifkan customer jika suspended
if ($customer->status === 'suspended') {
    $customer->update(['status' => 'active']);
}
```

### 3. Auto Suspend System
```php
// Suspend pelanggan terlambat >3 hari
$overduePayments = Payment::where('status', 'pending')
                         ->where('due_date', '<', now()->subDays(3))
                         ->get();

foreach ($overduePayments as $payment) {
    $payment->customer->update(['status' => 'suspended']);
}
```

## 🛠️ IMPLEMENTASI TEKNIS

### Database Structure
```sql
CREATE TABLE payments (
    id UUID PRIMARY KEY,
    customer_id UUID,
    invoice_number VARCHAR UNIQUE,
    amount DECIMAL(15,2),
    billing_date DATE,        -- Untuk periode apa
    due_date DATE,           -- Kapan harus bayar
    paid_date DATE NULL,     -- Kapan dibayar
    status ENUM('pending', 'paid', 'overdue'),
    notes TEXT,
    created_by UUID,
    confirmed_by UUID,       -- Admin yang konfirmasi
    timestamps
);
```

### Invoice Number Format
- **Format**: INV + YYYYMMDD + 4 digit sequence
- **Contoh**: INV202508110001, INV202508110002, dst.
- **Auto Generate**: Otomatis saat Payment dibuat

### Status Management
- **pending**: Belum bayar, masih dalam batas waktu
- **overdue**: Terlambat bayar (lewat due_date)
- **paid**: Sudah dibayar dan dikonfirmasi

## 📱 USER INTERFACE

### 1. Halaman Index (/payments)
- Statistik cards di atas
- Filter tabs (Belum Bayar/Terlambat/Sudah Bayar)
- Search box
- Tabel dengan pagination
- Action buttons

### 2. Halaman Detail (/payments/{id})
- Info tagihan lengkap
- Data pelanggan
- Info paket
- Riwayat pembayaran
- Button konfirmasi bayar

### 3. Color Coding
- **🟡 Kuning**: Belum bayar (pending)
- **🔴 Merah**: Terlambat (overdue)
- **🟢 Hijau**: Sudah bayar (paid)

## 🚀 FITUR ADVANCED

### 1. Auto Refresh
- Halaman auto refresh setiap 5 menit
- Real-time update status

### 2. Bulk Actions
- Auto suspend semua yang terlambat >3 hari
- Bulk payment confirmation (future)

### 3. Notifications (Future)
- Email reminder H-3 sebelum jatuh tempo
- SMS notification
- WhatsApp reminder

### 4. Reports (Future)
- Laporan pembayaran bulanan
- Analisis keterlambatan
- Revenue tracking

## 📈 BENEFITS

### Untuk Admin:
- **Monitoring Real-time**: Lihat semua tagihan pending
- **Efficient Management**: Konfirmasi pembayaran dengan 1 klik
- **Auto Suspend**: Tidak perlu manual suspend pelanggan nakal
- **Clear Overview**: Dashboard dengan statistik lengkap

### Untuk ISP Business:
- **Cash Flow**: Sistem pra-bayar memastikan cash flow positif
- **Reduced Risk**: Minimalisir pelanggan yang kabur tanpa bayar
- **Better Control**: Kontrol penuh atas aktivasi/deaktivasi
- **Scalable**: Mudah di-scale untuk ribuan pelanggan

## 🎮 CARA MENGGUNAKAN

### 1. Akses Menu
- Login → Management Pelanggan → Tagihan Belum Bayar

### 2. Monitor Tagihan
- Lihat statistik di dashboard cards
- Filter sesuai kebutuhan (Belum Bayar/Terlambat)
- Search pelanggan tertentu

### 3. Konfirmasi Pembayaran
- Klik tombol "Bayar" pada tagihan
- Konfirmasi → Otomatis generate tagihan bulan berikutnya
- Customer otomatis aktif jika sebelumnya suspended

### 4. Auto Suspend
- Klik "Auto Suspend Terlambat"
- Semua pelanggan terlambat >3 hari otomatis suspended

## ✅ READY TO USE!

Fitur Belum Bayar sudah **100% functional** dan siap digunakan:
- ✅ Database structure complete
- ✅ Models and relationships ready
- ✅ Controllers with full business logic
- ✅ Views with responsive UI
- ✅ Routes configured
- ✅ Auto payment generation
- ✅ Auto suspend system
- ✅ Manual payment confirmation

**Akses**: [http://localhost/wifiku/public/payments](http://localhost/wifiku/public/payments)
