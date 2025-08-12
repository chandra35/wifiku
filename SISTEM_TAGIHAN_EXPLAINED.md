# CARA KERJA SISTEM TAGIHAN DI APLIKASI WIFIKU

## 📋 OVERVIEW SISTEM TAGIHAN

Sistem tagihan di aplikasi WiFiku saat ini **masih dalam tahap dasar** dengan komponen-komponen berikut:

## 🏗️ STRUKTUR DATABASE TAGIHAN

### 1. Table `packages` (Paket Internet)
```sql
- id (UUID)
- name (Nama paket)
- price (Harga final termasuk PPN 11%)
- price_before_tax (Harga sebelum PPN)
- billing_cycle (Siklus tagihan)
- rate_limit (Kecepatan)
- is_active (Status aktif)
```

### 2. Table `customers` (Pelanggan)
```sql
- id (UUID)
- package_id (Relasi ke packages)
- billing_cycle (Siklus tagihan customer)
- next_billing_date (Tanggal tagihan berikutnya)
- installation_date (Tanggal pemasangan)
- status (active/inactive/suspended/terminated)
```

## ⚙️ CARA KERJA SAAT INI

### 1. Siklus Tagihan (Billing Cycle)
Aplikasi mendukung 4 jenis siklus tagihan:
- **monthly**: Bulanan (setiap bulan)
- **quarterly**: Triwulan (setiap 3 bulan)
- **semi-annual**: Semi-annual (setiap 6 bulan)
- **annual**: Tahunan (setiap 12 bulan)

### 2. Penetapan Tanggal Tagihan
- **Installation Date**: Tanggal pemasangan internet
- **Next Billing Date**: Tanggal tagihan berikutnya (diatur manual)
- **Billing Cycle**: Menentukan interval tagihan

### 3. Harga dan PPN
- **Price**: Harga final yang dibayar pelanggan (termasuk PPN 11%)
- **Price Before Tax**: Harga sebelum PPN (otomatis dihitung: price / 1.11)
- **PPN Amount**: PPN 11% (price - price_before_tax)

### 4. Status Pelanggan
- **Active**: Pelanggan aktif, bisa menggunakan internet
- **Inactive**: Pelanggan nonaktif
- **Suspended**: Pelanggan di-suspend (biasanya karena telat bayar)
- **Terminated**: Pelanggan diberhentikan

## 🔄 PROSES TAGIHAN MANUAL (SAAT INI)

### Langkah 1: Pembuatan Pelanggan
1. Admin input data pelanggan
2. Pilih paket internet
3. Set installation_date (tanggal pasang)
4. Set next_billing_date (tanggal tagihan pertama)
5. Set billing_cycle (bulanan/triwulan/6bulan/tahunan)

### Langkah 2: Monitoring Tagihan
- Admin harus manual mengecek tanggal tagihan
- Lihat di halaman detail pelanggan: "Tagihan Berikutnya"
- Cek status pembayaran manual

### Langkah 3: Update Tagihan
- Admin manual update next_billing_date setelah pelanggan bayar
- Admin bisa ubah status pelanggan (active/suspended)

## 📊 INFORMASI TAGIHAN DI UI

### 1. Halaman Detail Pelanggan (`customers/show`)
- **Paket**: Nama dan harga paket
- **Siklus Tagihan**: Monthly/Quarterly/Semi-annual/Annual
- **Tanggal Pasang**: Kapan internet dipasang
- **Tagihan Berikutnya**: Kapan harus bayar lagi
- **Status**: Active/Inactive/Suspended/Terminated

### 2. Riwayat Pembayaran
Di halaman show customer ada section "Riwayat Pembayaran" tapi masih **hardcoded/demo**.

## ❌ APA YANG BELUM ADA

### 1. Automatic Billing System
- Sistem belum otomatis generate tagihan
- Belum ada scheduler untuk membuat invoice
- Belum ada reminder pembayaran

### 2. Invoice/Bill Generation
- Belum ada table `invoices` atau `bills`
- Belum ada PDF invoice generator
- Belum ada email notification

### 3. Payment Processing
- Belum ada table `payments`
- Belum ada integrasi payment gateway
- Belum ada konfirmasi pembayaran otomatis

### 4. Automated Status Management
- Belum ada auto-suspend untuk telat bayar
- Belum ada auto-update next_billing_date
- Belum ada grace period management

## 🛠️ YANG BISA DIKEMBANGKAN

### 1. Automatic Invoice Generation
```sql
CREATE TABLE invoices (
    id UUID PRIMARY KEY,
    customer_id UUID,
    invoice_number VARCHAR,
    amount DECIMAL,
    due_date DATE,
    status ENUM('pending', 'paid', 'overdue', 'cancelled'),
    created_at TIMESTAMP
);
```

### 2. Payment Recording
```sql
CREATE TABLE payments (
    id UUID PRIMARY KEY,
    invoice_id UUID,
    customer_id UUID,
    amount DECIMAL,
    payment_method VARCHAR,
    payment_date TIMESTAMP,
    status ENUM('pending', 'completed', 'failed')
);
```

### 3. Laravel Scheduler untuk Auto-billing
```php
// app/Console/Commands/GenerateInvoices.php
// Jalankan setiap hari untuk cek tanggal tagihan
```

### 4. Email Notifications
- Reminder H-3 sebelum jatuh tempo
- Invoice baru
- Konfirmasi pembayaran
- Warning overdue

### 5. Payment Gateway Integration
- Midtrans
- Xendit
- Bank Transfer
- E-wallet

## 💡 KESIMPULAN

**Status saat ini**: Sistem tagihan masih **manual dan basic**
- ✅ Data structure sudah ada
- ✅ UI untuk display info billing sudah ada
- ❌ Automatic billing belum ada
- ❌ Payment processing belum ada
- ❌ Invoice generation belum ada

**Cara kerja sekarang**:
1. Admin set tanggal tagihan manual
2. Admin monitor manual kapan pelanggan harus bayar
3. Admin update manual setelah pelanggan bayar
4. Admin ubah status manual jika telat bayar

Untuk sistem yang lebih otomatis, perlu pengembangan lebih lanjut dengan fitur-fitur yang disebutkan di atas.
