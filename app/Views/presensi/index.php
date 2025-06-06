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
                <div class="btn-group">
                    <a href="/presensi/exportCsv/<?= $currentMonth ?>" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel"></i> Export CSV
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Month Navigation -->
            <div class="row mb-3">
                <div class="col">
                    <div class="btn-group" role="group">
                        <?php
                        $months = [
                            '01' => 'Januari',
                            '02' => 'Februari',
                            '03' => 'Maret',
                            '04' => 'April',
                            '05' => 'Mei',
                            '06' => 'Juni',
                            '07' => 'Juli',
                            '08' => 'Agustus',
                            '09' => 'September',
                            '10' => 'Oktober',
                            '11' => 'November',
                            '12' => 'Desember'
                        ];
                        foreach ($months as $num => $name):
                            $isActive = $num === $currentMonth;
                        ?>
                            <a href="/presensi?bulan=<?= $num ?>"
                                class="btn <?= $isActive ? 'btn-primary' : 'btn-outline-primary' ?>">
                                <?= $name ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

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
                                <?php elseif ($row['keterangan'] == 'tidak valid (di luar jam kerja)'): ?>
                                    <span class="badge bg-danger">Tidak Valid</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?= $row['keterangan'] ?></span>
                                <?php endif; ?>
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
        // Initialize DataTable
        const dataTable = $('#datatablesSimple').DataTable({
            order: [
                [1, 'desc'],
                [3, 'desc']
            ], // Sort by date and time
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
            }
        });
    });
</script>
<?= $this->endSection() ?>