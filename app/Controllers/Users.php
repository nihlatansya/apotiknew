<?php

namespace App\Controllers;

use App\Models\UserModel;

class Users extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $data['users'] = $this->userModel->where('role', 'karyawan')->findAll();
        return view('users/index', $data);
    }

    public function create()
    {
        return view('users/create');
    }

    public function store()
    {
        $rules = [
            'nama' => 'required|min_length[3]',
            'id_kartu_rfid' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Get nama and generate email from first name
        $nama = $this->request->getPost('nama');
        $namaParts = explode(' ', $nama);
        $firstName = strtolower($namaParts[0]);
        
        $data = [
            'nama' => $nama,
            'role' => 'karyawan',
            'id_kartu_rfid' => $this->request->getPost('id_kartu_rfid'),
            'email' => $firstName . '@sinamedika.com',
            'password' => md5('karyawan123'),
            'status' => 'aktif',
            'gender' => $this->request->getPost('gender')
        ];

        $this->userModel->insert($data);
        return redirect()->to('/users')->with('success', 'Karyawan berhasil ditambahkan');
    }

    public function edit($id)
    {
        $data['user'] = $this->userModel->find($id);
        if (!$data['user']) {
            return redirect()->to('/users')->with('error', 'Karyawan tidak ditemukan');
        }
        return view('users/edit', $data);
    }

    public function update($id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to('/users')->with('error', 'Karyawan tidak ditemukan');
        }

        $rules = [
            'nama' => 'required|min_length[3]',
            'id_kartu_rfid' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Get nama and generate email from first name
        $nama = $this->request->getPost('nama');
        $namaParts = explode(' ', $nama);
        $firstName = strtolower($namaParts[0]);

        $data = [
            'nama' => $nama,
            'id_kartu_rfid' => $this->request->getPost('id_kartu_rfid'),
            'email' => $firstName . '@sinamedika.com',
            'status' => $this->request->getPost('status'),
            'gender' => $this->request->getPost('gender')
        ];

        $this->userModel->update($id, $data);
        return redirect()->to('/users')->with('success', 'Karyawan berhasil diperbarui');
    }

    public function delete($id)
    {
        // Prevent self-deletion
        if ($id == session()->get('iduser')) {
            return redirect()->to('/users')->with('error', 'Tidak dapat menghapus akun sendiri');
        }

        $this->userModel->delete($id);
        return redirect()->to('/users')->with('success', 'Karyawan berhasil dihapus');
    }
}
