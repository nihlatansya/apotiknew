<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        return view('auth/login');
    }

    public function login()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $user = $this->userModel->verifyPassword($username, $password);

        if ($user) {
            $sessionData = [
                'user_id' => $user['iduser'],
                'username' => $user['nama'],
                'role' => $user['role'],
                'logged_in' => true
            ];
            session()->set($sessionData);
            return redirect()->to('/presensi');
        }

        return redirect()->back()->with('error', 'Username atau password salah');
    }

    public function register()
    {
        return view('auth/register');
    }

    public function processRegister()
    {
        $rules = [
            'username' => 'required|min_length[3]|is_unique[tb_user.nama]',
            'email' => 'required|valid_email',
            'password' => 'required|min_length[6]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nama' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'password' => md5($this->request->getPost('password')),
            'role' => 'admin',
            'status' => 'aktif',
            'tb_jadwal_shift_id_jadwal_shift' => 1 // Default shift ID
        ];

        $this->userModel->insert($data);
        return redirect()->to('/login')->with('success', 'Registrasi berhasil, silakan login');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
