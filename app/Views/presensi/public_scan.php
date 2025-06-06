<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Presensi</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .scan-container {
            max-width: 800px;
            margin: 50px auto;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #eee;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }

        .card-body {
            padding: 30px;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px;
            font-size: 16px;
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .alert {
            border-radius: 10px;
            padding: 20px;
        }

        .user-icon {
            color: #0d6efd;
            margin-bottom: 15px;
        }

        .countdown {
            font-size: 14px;
            color: #6c757d;
            margin-top: 15px;
        }

        .refresh-alert {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            background-color: #fff;
            padding: 15px 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            font-size: 18px;
            color: #0d6efd;
            display: none;
        }

        .refresh-alert i {
            margin-right: 10px;
        }
    </style>
</head>

<body>
    <!-- Refresh Alert -->
    <div id="refreshAlert" class="refresh-alert">
        <i class="fas fa-sync-alt fa-spin"></i>
        <span>Halaman akan di-refresh dalam <span id="countdown">2</span> detik...</span>
    </div>

    <div class="scan-container">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0 text-center">
                    <i class="fas fa-id-card me-2"></i>
                    Scan Presensi Karyawan
                </h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="rfid" class="form-label">Scan Kartu RFID</label>
                            <input type="text" class="form-control" id="rfid" name="rfid" autofocus>
                            <small class="form-text text-muted">Tempelkan kartu RFID Anda di reader</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div id="result" class="alert" style="display: none;">
                            <div class="text-center mb-3">
                                <i class="fas fa-user-circle fa-3x user-icon"></i>
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

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

                // Disable input field temporarily
                inputField.disabled = true;
                inputField.style.backgroundColor = '#e9ecef';
                inputField.style.cursor = 'not-allowed';

                // Kirim data ke server
                fetch('/scan-rfid', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                        },
                        body: JSON.stringify({
                            rfid: rfid
                        })
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

                            // Tampilkan alert refresh
                            const refreshAlert = document.getElementById('refreshAlert');
                            refreshAlert.style.display = 'block';

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

                            // Jika error karena cooldown
                            if (data.message.includes('Mohon tunggu')) {
                                // Tampilkan alert refresh
                                const refreshAlert = document.getElementById('refreshAlert');
                                refreshAlert.style.display = 'block';

                                // Countdown untuk refresh halaman (2 detik)
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
</body>

</html>