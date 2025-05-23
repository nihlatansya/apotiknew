<?php

namespace App\Models;

use CodeIgniter\Model;

class JadwalShiftModel extends Model
{
    protected $table = 'tb_jadwal_shift';
    protected $primaryKey = 'id_jadwal_shift';
    protected $allowedFields = ['dari_tanggal', 'sampai_tanggal', 'shift_mulai', 'shift_selesai', 'jenis_shift'];
    protected $useTimestamps = false;
}
