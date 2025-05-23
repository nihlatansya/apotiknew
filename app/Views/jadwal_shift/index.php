<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Jadwal Shift<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid px-4">
    <h1 class="mt-4">Jadwal Shift</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Jadwal Shift</li>
    </ol>

    <?php if (session()->has('success')) : ?>
        <div class="alert alert-success">
            <?= session('success') ?>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Daftar Jadwal Shift
            <a href="/jadwal-shift/create" class="btn btn-primary btn-sm float-end">
                <i class="fas fa-plus"></i> Tambah Jadwal
            </a>
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Dari Tanggal</th>
                        <th>Sampai Tanggal</th>
                        <th>Shift Mulai</th>
                        <th>Shift Selesai</th>
                        <th>Jenis Shift</th>
                        <th>Karyawan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jadwal_shift as $shift) : ?>
                        <tr>
                            <td><?= $shift['id_jadwal_shift'] ?></td>
                            <td><?= date('d/m/Y', strtotime($shift['dari_tanggal'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($shift['sampai_tanggal'])) ?></td>
                            <td><?= $shift['shift_mulai'] ?></td>
                            <td><?= $shift['shift_selesai'] ?></td>
                            <td><?= ucfirst($shift['jenis_shift']) ?></td>
                            <td>
                                <?php if (!empty($shift['users'])): ?>
                                    <ul class="list-unstyled mb-0">
                                        <?php foreach ($shift['users'] as $user): ?>
                                            <li><?= $user['nama'] ?> (<?= $user['role'] ?>)</li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <span class="text-muted">Belum ada karyawan</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="/jadwal-shift/edit/<?= $shift['id_jadwal_shift'] ?>" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="/jadwal-shift/delete/<?= $shift['id_jadwal_shift'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus jadwal ini?')">
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
<?= $this->endSection() ?>