<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'tb_user';
    protected $primaryKey = 'iduser';
    protected $allowedFields = ['nama', 'email', 'password', 'role', 'status', 'gender', 'id_kartu_rfid', 'id_jadwal_shift'];
    protected $useTimestamps = false;


    public function verifyPassword($username, $password)
    {
        $user = $this->where('nama', $username)->first();
        if ($user && $user['password'] === md5($password)) {
            return $user;
        }
        return false;
    }

    public function beforeInsert(array $data)
    {
        // Jika role karyawan dan password kosong, set password default
        if ($data['data']['role'] === 'karyawan') {
            if (empty($data['data']['password'])) {
                $data['data']['password'] = password_hash('karyawan123', PASSWORD_DEFAULT);
            }
            if (empty($data['data']['email'])) {
                $data['data']['email'] = strtolower(str_replace(' ', '', $data['data']['nama'])) . '@sinamedika.com';
            }
        } else if (!empty($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        }
        return $data;
    }

    public function beforeUpdate(array $data)
    {
        // Jika password diisi, hash password
        if (!empty($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        } else {
            // Jika password kosong, hapus dari data yang akan diupdate
            unset($data['data']['password']);
        }

        // Jika role karyawan dan email kosong, set email default
        if ($data['data']['role'] === 'karyawan' && empty($data['data']['email'])) {
            $data['data']['email'] = strtolower(str_replace(' ', '', $data['data']['nama'])) . '@sinamedika.com';
        }

        return $data;
    }
}
