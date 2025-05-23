<?php

namespace App\Models;

use CodeIgniter\Model;

class PresensiModel extends Model
{
    protected $table = 'tb_presensi';
    protected $primaryKey = 'id_presensi';
    protected $allowedFields = [
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'keterangan',
        'dibuat_pada',
        'diupdate_pada',
        'tb_user_iduser',
        'tb_jadwal_shift_id_jadwal_shift'
    ];
    protected $useTimestamps = false;

    public function getPresensiWithUser()
    {
        return $this->select('tb_presensi.*, tb_user.nama, tb_jadwal_shift.jenis_shift')
            ->join('tb_user', 'tb_user.iduser = tb_presensi.tb_user_iduser')
            ->join('tb_jadwal_shift', 'tb_jadwal_shift.id_jadwal_shift = tb_presensi.tb_jadwal_shift_id_jadwal_shift')
            ->findAll();
    }
}
