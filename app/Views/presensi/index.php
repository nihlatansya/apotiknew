<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Presensi<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid px-4">
    <h1 class="mt-4">Presensi</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Presensi</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-clock me-1"></i>
            Data Presensi
            <div class="float-end d-flex gap-2">
                <select class="form-select form-select-sm" id="bulan" style="width: auto;" onchange="filterByMonth()">
                    <option value="01">Januari</option>
                    <option value="02">Februari</option>
                    <option value="03">Maret</option>
                    <option value="04">April</option>
                    <option value="05">Mei</option>
                    <option value="06">Juni</option>
                    <option value="07">Juli</option>
                    <option value="08">Agustus</option>
                    <option value="09">September</option>
                    <option value="10">Oktober</option>
                    <option value="11">November</option>
                    <option value="12">Desember</option>
                </select>
                <a href="#" class="btn btn-success btn-sm" onclick="exportCsv()">
                    <i class="fas fa-file-excel"></i> Export CSV
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Loading Indicator -->
            <div id="loadingIndicator" class="text-center d-none">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Memuat data...</p>
            </div>

            <div class="table-responsive">
                <table class="table table-striped" id="presensiTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Nama Karyawan</th>
                            <th>Jam Masuk</th>
                            <th>Jam Pulang</th>
                            <th>Persentase</th>
                            <th>Keterangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; ?>
                        <?php foreach ($presensi as $row): ?>
                            <tr>
                                <td><?= $i++; ?></td>
                                <td><?= date('d-m-Y', strtotime($row['tanggal'])); ?></td>
                                <td><?= $row['nama'] ?></td>
                                <td><?= $row['jam_masuk'] ?></td>
                                <td><?= $row['jam_pulang'] ?></td>
                                <td><?= number_format($row['persentase'], 2); ?>%</td>
                                <td>
                                    <span class="badge bg-<?= $row['keterangan'] === 'hadir' ? 'success' : ($row['keterangan'] === 'telat' ? 'warning' : 'danger') ?>">
                                        <?= ucfirst($row['keterangan']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="/presensi/edit/<?= $row['id_presensi'] ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="/presensi/delete/<?= $row['id_presensi'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- RFID Scanner Modal -->
<div class="modal fade" id="rfidModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Scan RFID</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="rfid" class="form-label">ID Kartu RFID</label>
                    <input type="text" class="form-control" id="rfid" name="rfid" autofocus>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="scanRfid()">Scan</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Set bulan dropdown ke bulan saat ini
    document.getElementById('bulan').value = new Date().toLocaleString('default', {
        month: '2-digit'
    });

    function filterByMonth() {
        const bulan = document.getElementById('bulan').value;
        const loadingIndicator = document.getElementById('loadingIndicator');
        const table = document.getElementById('presensiTable');

        // Show loading indicator
        loadingIndicator.classList.remove('d-none');
        table.classList.add('d-none');

        // Fetch data for selected month
        fetch(`/presensi/getByMonth/${bulan}`)
            .then(response => response.json())
            .then(data => {
                const tbody = table.querySelector('tbody');
                tbody.innerHTML = '';

                data.forEach((row, index) => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${index + 1}</td>
                        <td>${formatDate(row.tanggal)}</td>
                        <td>${row.nama}</td>
                        <td>${row.jam_masuk || '-'}</td>
                        <td>${row.jam_pulang || '-'}</td>
                        <td>${formatPersentase(row.persentase)}</td>
                        <td>
                            <span class="badge bg-${getBadgeColor(row.keterangan)}">
                                ${capitalizeFirst(row.keterangan)}
                            </span>
                        </td>
                        <td>
                            <a href="/presensi/edit/${row.id_presensi}" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="/presensi/delete/${row.id_presensi}" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });

                // Show success notification
                showNotification('Data berhasil diperbarui', 'success');
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Terjadi kesalahan saat memuat data', 'error');
            })
            .finally(() => {
                // Hide loading indicator and show table
                loadingIndicator.classList.add('d-none');
                table.classList.remove('d-none');
            });
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }

    function formatPersentase(persentase) {
        return Number(persentase).toFixed(2) + '%';
    }

    function getBadgeColor(keterangan) {
        switch (keterangan) {
            case 'hadir':
                return 'success';
            case 'telat':
                return 'warning';
            default:
                return 'danger';
        }
    }

    function capitalizeFirst(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    function showNotification(message, type = 'success') {
        // Remove any existing notifications
        const existingToasts = document.querySelectorAll('.toast');
        existingToasts.forEach(toast => toast.remove());

        const toast = document.createElement('div');
        toast.className = `toast align-items-center border-0 position-fixed top-0 end-0 m-3`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');

        // Set background and text color based on type
        let bgColor, textColor;
        switch (type) {
            case 'success':
                bgColor = '#d4edda';
                textColor = '#155724';
                break;
            case 'error':
                bgColor = '#f8d7da';
                textColor = '#721c24';
                break;
            default:
                bgColor = '#cce5ff';
                textColor = '#004085';
        }

        toast.style.backgroundColor = bgColor;
        toast.style.color = textColor;

        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

        document.body.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast, {
            autohide: true,
            delay: 3000
        });
        bsToast.show();

        // Remove toast after it's hidden
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }

    function exportCsv() {
        const bulan = document.getElementById('bulan').value;
        window.location.href = `/presensi/exportCsv/${bulan}`;
    }

    // Initial load
    filterByMonth();

    // Auto focus on RFID input when modal opens
    document.getElementById('rfidModal').addEventListener('shown.bs.modal', function() {
        document.getElementById('rfid').focus();
    });
</script>
<?= $this->endSection() ?>