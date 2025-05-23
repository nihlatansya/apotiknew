<?php

namespace App\Controllers;

use App\Models\JadwalShiftModel;
use App\Models\UserModel;

class JadwalShift extends BaseController
{
    protected $jadwalShiftModel;
    protected $userModel;
    protected $db;

    public function __construct()
    {
        $this->jadwalShiftModel = new JadwalShiftModel();
        $this->userModel = new UserModel();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $data['jadwal_shift'] = $this->jadwalShiftModel->findAll();
        // Get users for each jadwal shift
        foreach ($data['jadwal_shift'] as &$shift) {
            $shift['users'] = $this->userModel->where('id_jadwal_shift', $shift['id_jadwal_shift'])->findAll();
        }
        return view('jadwal_shift/index', $data);
    }

    public function create()
    {
        $data['users'] = $this->userModel->where('role', 'karyawan')
                                       ->where('id_jadwal_shift IS NULL')
                                       ->findAll();
        return view('jadwal_shift/create', $data);
    }

    public function store()
    {
        $data = [
            'dari_tanggal' => $this->request->getPost('dari_tanggal'),
            'sampai_tanggal' => $this->request->getPost('sampai_tanggal'),
            'shift_mulai' => $this->request->getPost('shift_mulai'),
            'shift_selesai' => $this->request->getPost('shift_selesai'),
            'jenis_shift' => $this->request->getPost('jenis_shift'),
        ];

        $this->db->transStart();

        // Insert jadwal shift
        $id_jadwal_shift = $this->jadwalShiftModel->insert($data);

        // Update selected users
        $selected_users = $this->request->getPost('users');
        if ($selected_users) {
            $this->userModel->whereIn('iduser', $selected_users)
                ->set(['id_jadwal_shift' => $id_jadwal_shift])
                ->update();
        }

        $this->db->transComplete();

        return redirect()->to('/jadwal-shift')->with('success', 'Jadwal shift berhasil ditambahkan');
    }

    public function edit($id)
    {
        $data['jadwal_shift'] = $this->jadwalShiftModel->find($id);
        // Get users assigned to this shift
        $data['assigned_users'] = $this->userModel->where('id_jadwal_shift', $id)->findAll();
        // Get users not assigned to any shift
        $data['available_users'] = $this->userModel->where('id_jadwal_shift IS NULL')->findAll();
        return view('jadwal_shift/edit', $data);
    }

    public function update($id)
    {
        $data = [
            'dari_tanggal' => $this->request->getPost('dari_tanggal'),
            'sampai_tanggal' => $this->request->getPost('sampai_tanggal'),
            'shift_mulai' => $this->request->getPost('shift_mulai'),
            'shift_selesai' => $this->request->getPost('shift_selesai'),
            'jenis_shift' => $this->request->getPost('jenis_shift'),
        ];

        $this->db->transStart();

        // Update jadwal shift
        $this->jadwalShiftModel->update($id, $data);

        // Reset all users from this shift
        $this->userModel->where('id_jadwal_shift', $id)
            ->set(['id_jadwal_shift' => null])
            ->update();

        // Update selected users
        $selected_users = $this->request->getPost('users');
        if ($selected_users) {
            $this->userModel->whereIn('iduser', $selected_users)
                ->set(['id_jadwal_shift' => $id])
                ->update();
        }

        $this->db->transComplete();

        return redirect()->to('/jadwal-shift')->with('success', 'Jadwal shift berhasil diupdate');
    }

    public function delete($id)
    {
        $this->db->transStart();

        // Reset users from this shift
        $this->userModel->where('id_jadwal_shift', $id)
            ->set(['id_jadwal_shift' => null])
            ->update();

        // Delete the shift
        $this->jadwalShiftModel->delete($id);

        $this->db->transComplete();

        return redirect()->to('/jadwal-shift')->with('success', 'Jadwal shift berhasil dihapus');
    }
}
