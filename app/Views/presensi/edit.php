<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Edit Presensi<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-body">
        <h4 class="card-title mb-4">Edit Presensi</h4>

        <form action="/presensi/update/<?= $presensi['id_presensi'] ?>" method="post">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Karyawan</label>
                        <select class="form-select" id="user_id" name="user_id" required>
                            <option value="">Pilih Karyawan</option>
                            <?php foreach ($users as $user): ?>
                            <option value="<?= $user['iduser'] ?>" <?= $user['iduser'] == $presensi['tb_user_iduser'] ? 'selected' : '' ?>>
                                <?= $user['nama'] ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="tanggal" class="form-label">Tanggal</label>
                        <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?= $presensi['tanggal'] ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="jam_masuk" class="form-label">Jam Masuk</label>
                        <input type="time" class="form-control" id="jam_masuk" name="jam_masuk" value="<?= $presensi['jam_masuk'] ?>" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="jam_pulang" class="form-label">Jam Pulang</label>
                        <input type="time" class="form-control" id="jam_pulang" name="jam_pulang" value="<?= $presensi['jam_pulang']; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="persentase" class="form-label">Persentase</label>
                        <input type="text" class="form-control" id="persentase" value="<?= number_format($presensi['persentase'], 2); ?>%" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <select class="form-select" id="keterangan" name="keterangan" required>
                            <option value="hadir" <?= $presensi['keterangan'] == 'hadir' ? 'selected' : '' ?>>Hadir</option>
                            <option value="telat" <?= $presensi['keterangan'] == 'telat' ? 'selected' : '' ?>>Telat</option>
                            <option value="absen" <?= $presensi['keterangan'] == 'absen' ? 'selected' : '' ?>>Absen</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="/presensi" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?> 