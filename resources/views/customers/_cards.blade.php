<div class="row mb-3">
    <!-- Card: Jumlah Pelanggan Aktif -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ \App\Models\Customer::where('status', 'active')->count() }}</h3>
                <p>Jumlah Pelanggan Aktif</p>
                <div class="mt-2">
                    <span class="text-white-50">Total Uang Masuk:</span><br>
                    <span class="h4" id="total-paid-value">*****</span>
                    <button id="toggle-total-paid" class="btn btn-light btn-sm ml-2" style="vertical-align: middle; margin-top:-5px;"><i class="fas fa-eye"></i></button>
                </div>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>
    <!-- Card: Pelanggan Belum Bayar Bulan Ini -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ \App\Models\Payment::where('status', 'pending')->whereMonth('billing_date', now()->month)->whereYear('billing_date', now()->year)->distinct('customer_id')->count('customer_id') }}</h3>
                <p>Pelanggan Belum Bayar Bulan Ini</p>
                <div class="mt-2">
                    <span class="text-dark-50">Total Nominal:</span><br>
                    <span class="h4" id="total-unpaid-value">*****</span>
                    <button id="toggle-total-unpaid" class="btn btn-light btn-sm ml-2" style="vertical-align: middle; margin-top:-5px;"><i class="fas fa-eye"></i></button>
                </div>
            </div>
            <div class="icon">
                <i class="fas fa-user-clock"></i>
            </div>
        </div>
    </div>
    <!-- Card: Pelanggan Berhenti -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ \App\Models\Customer::where('status', 'terminated')->count() }}</h3>
                <p>Pelanggan Berhenti</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-slash"></i>
            </div>
        </div>
    </div>
    <!-- Card: Jumlah Pelanggan Isolir (Suspended) -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>{{ \App\Models\Customer::where('status', 'suspended')->count() }}</h3>
                <p>Jumlah Pelanggan Isolir</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-lock"></i>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show/hide for Total Uang Masuk
    const btnPaid = document.getElementById('toggle-total-paid');
    const paidValue = document.getElementById('total-paid-value');
    let paidShown = false;
    const paidReal = 'Rp {{ number_format(\App\Models\Payment::where('status', 'paid')->sum('amount'), 0, ',', '.') }}';
    if(btnPaid && paidValue) {
        btnPaid.addEventListener('click', function(e) {
            e.preventDefault();
            if (!paidShown) {
                paidValue.textContent = paidReal;
                btnPaid.innerHTML = '<i class="fas fa-eye-slash"></i>';
                paidShown = true;
            } else {
                paidValue.textContent = '*****';
                btnPaid.innerHTML = '<i class="fas fa-eye"></i>';
                paidShown = false;
            }
        });
    }

    // Show/hide for Total Nominal Belum Bayar Bulan Ini
    const btnUnpaid = document.getElementById('toggle-total-unpaid');
    const unpaidValue = document.getElementById('total-unpaid-value');
    let unpaidShown = false;
    const unpaidReal = 'Rp {{ number_format(\App\Models\Payment::where('status', 'pending')->whereMonth('billing_date', now()->month)->whereYear('billing_date', now()->year)->sum('amount'), 0, ',', '.') }}';
    if(btnUnpaid && unpaidValue) {
        btnUnpaid.addEventListener('click', function(e) {
            e.preventDefault();
            if (!unpaidShown) {
                unpaidValue.textContent = unpaidReal;
                btnUnpaid.innerHTML = '<i class="fas fa-eye-slash"></i>';
                unpaidShown = true;
            } else {
                unpaidValue.textContent = '*****';
                btnUnpaid.innerHTML = '<i class="fas fa-eye"></i>';
                unpaidShown = false;
            }
        });
    }
    const btn = document.getElementById('toggle-nominal');
    const nominal = document.getElementById('total-nominal');
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        if (nominal.style.display === 'none') {
            nominal.style.display = 'block';
            btn.innerHTML = '<i class="fas fa-eye-slash"></i> Hide';
        } else {
            nominal.style.display = 'none';
            btn.innerHTML = '<i class="fas fa-eye"></i> Show';
        }
    });
});
</script>
