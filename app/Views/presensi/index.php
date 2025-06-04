<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Data Presensi<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid px-4">
    <h1 class="mt-4">Data Presensi</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Data Presensi</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Data Presensi
            <div class="float-end">
                <a href="/presensi/scan" class="btn btn-primary btn-sm">
                    <i class="fas fa-id-card"></i> Scan RFID
                </a>
                <a href="/presensi/exportCsv" class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel"></i> Export CSV
                </a>
            </div>
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Nama Karyawan</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Shift</th>
                        <th>Persentase</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    <?php foreach ($presensi as $row): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
                        <td><?= $row['nama'] ?></td>
                        <td><?= $row['jam_masuk'] ?? '-' ?></td>
                        <td><?= $row['jam_pulang'] ?? '-' ?></td>
                        <td><?= $row['jenis_shift'] ?></td>
                        <td><?= number_format($row['persentase'], 2) ?>%</td>
                        <td>
                            <?php if ($row['keterangan'] == 'telat'): ?>
                                <span class="badge bg-warning">Terlambat</span>
                            <?php elseif ($row['keterangan'] == 'hadir'): ?>
                                <span class="badge bg-success">Hadir</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?= $row['keterangan'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/presensi/edit/<?= $row['id_presensi'] ?>" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="/presensi/delete/<?= $row['id_presensi'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
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

<script>
$(document).ready(function() {
    $('#datatablesSimple').DataTable({
        order: [[1, 'desc'], [3, 'desc']], // Sort by date and time
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
        }
    });
});
</script>
<?= $this->endSection() ?>