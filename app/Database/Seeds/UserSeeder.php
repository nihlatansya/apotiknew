<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        //
        $data = [
            'nama'     => 'Budi',
            'role'     => 'karyawan',
            'status'   => 'aktif',
            'password' => md5('rahasia'), // Gunakan password_hash di aplikasi nyata
            'gender'   => 'laki-laki',
            'tb_jadwal_shift_id_jadwal_shift' => 1
        ];

        $this->db->table('tb_user')->insert($data);
    }
}
