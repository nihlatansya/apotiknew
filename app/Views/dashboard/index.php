<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-4">
        <div class="card bg-primary text-white mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Total Karyawan</h6>
                        <h2 class="mb-0"><?= $total_users ?></h2>
                    </div>
                    <i class="fas fa-users fa-2x"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card bg-success text-white mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Total Presensi</h6>
                        <h2 class="mb-0"><?= $total_presensi ?></h2>
                    </div>
                    <i class="fas fa-clipboard-list fa-2x"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card bg-info text-white mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Presensi Hari Ini</h6>
                        <h2 class="mb-0"><?= $presensi_hari_ini ?></h2>
                    </div>
                    <i class="fas fa-calendar-check fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">Selamat Datang di Sistem Informasi Sina Medika</h4>
                <p class="card-text">
                    Sistem ini digunakan untuk mengelola presensi karyawan menggunakan RFID.
                    Anda dapat melakukan scan RFID untuk mencatat kehadiran karyawan.
                </p>
                <a href="/presensi" class="btn btn-primary">
                    <i class="fas fa-clipboard-list"></i> Lihat Data Presensi
                </a>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>