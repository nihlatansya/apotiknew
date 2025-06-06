<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Scan Presensi<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid px-4">
    <h1 class="mt-4">Scan Presensi</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Scan Presensi</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-id-card me-1"></i>
            Scan Kartu RFID
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="rfid">Scan Kartu RFID</label>
                        <input type="text" class="form-control" id="rfid" name="rfid" autofocus>
                        <small class="form-text text-muted">Tempelkan kartu RFID Anda di reader</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div id="result" class="alert" style="display: none;">
                        <div class="text-center mb-3">
                            <i class="fas fa-user-circle fa-3x mb-2"></i>
                            <h4 id="nama-karyawan" class="mb-2"></h4>
                        </div>
                        <div class="text-center">
                            <p id="status-absensi" class="mb-2"></p>
                            <p id="jam-masuk" class="mb-2"></p>
                            <p id="jam-pulang" class="mb-2"></p>
                            <p id="keterangan" class="mb-0"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('rfid').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const rfid = this.value;
        const inputField = this;
        
        if (!rfid) {
            alert('ID RFID tidak boleh kosong');
            this.value = '';
            this.focus();
            return;
        }
        
        // Disable input field
        inputField.disabled = true;
        inputField.style.backgroundColor = '#e9ecef';
        inputField.style.cursor = 'not-allowed';
        
        // Kirim data ke server
        fetch('/presensi/scan-rfid', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
            },
            body: JSON.stringify({ rfid: rfid })
        })
        .then(response => response.json())
        .then(data => {
            const resultDiv = document.getElementById('result');
            resultDiv.style.display = 'block';
            
            if (data.status === 'success') {
                resultDiv.className = 'alert alert-success';
                
                // Update tampilan
                document.getElementById('nama-karyawan').textContent = data.data.nama;
                document.getElementById('status-absensi').textContent = data.message;
                
                if (data.data.jam_masuk) {
                    document.getElementById('jam-masuk').textContent = `Jam Masuk: ${data.data.jam_masuk}`;
                }
                
                if (data.data.jam_pulang) {
                    document.getElementById('jam-pulang').textContent = `Jam Pulang: ${data.data.jam_pulang}`;
                }
                
                if (data.data.keterangan) {
                    document.getElementById('keterangan').textContent = `Status: ${data.data.keterangan}`;
                }

                // Tambahkan pesan countdown
                const countdownDiv = document.createElement('div');
                countdownDiv.className = 'text-center mt-3';
                countdownDiv.innerHTML = '<small class="text-muted">Halaman akan di-refresh dalam <span id="countdown">2</span> detik...</small>';
                resultDiv.appendChild(countdownDiv);

                // Countdown
                let count = 2;
                const countdownElement = document.getElementById('countdown');
                const countdownInterval = setInterval(() => {
                    count--;
                    countdownElement.textContent = count;
                    if (count <= 0) {
                        clearInterval(countdownInterval);
                        window.location.reload();
                    }
                }, 1000);
            } else {
                resultDiv.className = 'alert alert-danger';
                document.getElementById('nama-karyawan').textContent = '';
                document.getElementById('status-absensi').textContent = data.message;
                document.getElementById('jam-masuk').textContent = '';
                document.getElementById('jam-pulang').textContent = '';
                document.getElementById('keterangan').textContent = '';

                // Jika error karena cooldown, refresh setelah waktu cooldown
                if (data.message.includes('Mohon tunggu')) {
                    const seconds = parseInt(data.message.match(/\d+/)[0]);
                    
                    // Tambahkan pesan countdown
                    const countdownDiv = document.createElement('div');
                    countdownDiv.className = 'text-center mt-3';
                    countdownDiv.innerHTML = `<small class="text-muted">Halaman akan di-refresh dalam <span id="countdown">${seconds}</span> detik...</small>`;
                    resultDiv.appendChild(countdownDiv);

                    // Countdown
                    let count = seconds;
                    const countdownElement = document.getElementById('countdown');
                    const countdownInterval = setInterval(() => {
                        count--;
                        countdownElement.textContent = count;
                        if (count <= 0) {
                            clearInterval(countdownInterval);
                            window.location.reload();
                        }
                    }, 1000);
                } else {
                    // Enable input field kembali jika bukan error cooldown
                    inputField.disabled = false;
                    inputField.style.backgroundColor = '';
                    inputField.style.cursor = '';
                }
            }
            
            // Reset input
            inputField.value = '';
        })
        .catch(error => {
            console.error('Error:', error);
            const resultDiv = document.getElementById('result');
            resultDiv.style.display = 'block';
            resultDiv.className = 'alert alert-danger';
            document.getElementById('nama-karyawan').textContent = '';
            document.getElementById('status-absensi').textContent = 'Terjadi kesalahan saat memproses data';
            document.getElementById('jam-masuk').textContent = '';
            document.getElementById('jam-pulang').textContent = '';
            document.getElementById('keterangan').textContent = '';
            
            // Enable input field kembali
            inputField.disabled = false;
            inputField.style.backgroundColor = '';
            inputField.style.cursor = '';
        });
    }
});
</script>
<?= $this->endSection() ?> 