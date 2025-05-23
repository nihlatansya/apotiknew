<?php

namespace App\Controllers;

use App\Models\PresensiModel;
use App\Models\UserModel;
use App\Models\JadwalShiftModel;

class Presensi extends BaseController
{
    protected $presensiModel;
    protected $userModel;
    protected $jadwalShiftModel;

    public function __construct()
    {
        $this->presensiModel = new PresensiModel();
        $this->userModel = new UserModel();
        $this->jadwalShiftModel = new JadwalShiftModel();
    }

    public function index()
    {
        $data['presensi'] = $this->presensiModel->getPresensiWithUser();
        return view('presensi/index', $data);
    }

    public function create()
    {
        $data['users'] = $this->userModel->findAll();
        return view('presensi/create', $data);
    }

    public function store()
    {
        $data = [
            'tanggal' => $this->request->getPost('tanggal'),
            'jam_masuk' => $this->request->getPost('jam_masuk'),
            'jam_pulang' => $this->request->getPost('jam_pulang'),
            'keterangan' => $this->request->getPost('keterangan'),
            'tb_user_iduser' => $this->request->getPost('user_id'),
            'tb_jadwal_shift_id_jadwal_shift' => $this->request->getPost('jadwal_shift_id'),
            'dibuat_pada' => date('Y-m-d H:i:s'),
            'diupdate_pada' => date('Y-m-d H:i:s')
        ];

        $this->presensiModel->insert($data);
        return redirect()->to('/presensi')->with('success', 'Data presensi berhasil ditambahkan');
    }

    public function edit($id)
    {
        $data['presensi'] = $this->presensiModel->find($id);
        $data['users'] = $this->userModel->findAll();
        return view('presensi/edit', $data);
    }

    public function update($id)
    {
        $data = [
            'tanggal' => $this->request->getPost('tanggal'),
            'jam_masuk' => $this->request->getPost('jam_masuk'),
            'jam_pulang' => $this->request->getPost('jam_pulang'),
            'keterangan' => $this->request->getPost('keterangan'),
            'tb_user_iduser' => $this->request->getPost('user_id'),
            'tb_jadwal_shift_id_jadwal_shift' => $this->request->getPost('jadwal_shift_id'),
            'diupdate_pada' => date('Y-m-d H:i:s')
        ];

        $this->presensiModel->update($id, $data);
        return redirect()->to('/presensi')->with('success', 'Data presensi berhasil diupdate');
    }

    public function delete($id)
    {
        $this->presensiModel->delete($id);
        return redirect()->to('/presensi')->with('success', 'Data presensi berhasil dihapus');
    }

    public function exportCsv($bulan = null)
    {
        // Jika bulan tidak diisi, gunakan bulan saat ini
        if (!$bulan) {
            $bulan = date('m');
        }

        // Ambil data presensi untuk bulan yang dipilih
        $presensi = $this->presensiModel->getPresensiWithUser()
            ->where('MONTH(tanggal)', $bulan)
            ->where('YEAR(tanggal)', date('Y'))
            ->findAll();

        $filename = 'presensi_' . date('Y') . '_' . $bulan . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // Add CSV headers
        fputcsv($output, ['No', 'Tanggal', 'Nama Karyawan', 'Jam Masuk', 'Jam Pulang', 'Persentase', 'Keterangan']);

        // Add data rows
        $no = 1;
        foreach ($presensi as $row) {
            fputcsv($output, [
                $no++,
                date('d-m-Y', strtotime($row['tanggal'])),
                $row['nama'],
                $row['jam_masuk'],
                $row['jam_pulang'],
                number_format($row['persentase'], 2) . '%',
                $row['keterangan']
            ]);
        }

        fclose($output);
        exit;
    }

    public function scanRfid()
    {
        $rfid = $this->request->getPost('rfid');
        
        // Debug untuk melihat RFID yang diterima
        log_message('debug', 'RFID yang diterima: ' . $rfid);
        
        $user = $this->userModel->where('id_kartu_rfid', $rfid)->first();
        
        if (!$user) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Kartu RFID tidak terdaftar'
            ]);
        }

        // Debug untuk melihat data user secara detail
        log_message('debug', 'Data User Detail:');
        log_message('debug', 'ID User: ' . $user['iduser']);
        log_message('debug', 'Nama: ' . $user['nama']);
        log_message('debug', 'ID Jadwal Shift: ' . (isset($user['id_jadwal_shift']) ? $user['id_jadwal_shift'] : 'null'));
        log_message('debug', 'Tipe ID Jadwal Shift: ' . (isset($user['id_jadwal_shift']) ? gettype($user['id_jadwal_shift']) : 'undefined'));

        // Cek apakah user memiliki jadwal shift
        if (!isset($user['id_jadwal_shift']) || $user['id_jadwal_shift'] === null || $user['id_jadwal_shift'] === '') {
            log_message('error', 'User tidak memiliki jadwal shift: ' . json_encode($user));
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User belum memiliki jadwal shift'
            ]);
        }

        // Cek jadwal shift
        $jadwalShift = $this->jadwalShiftModel->find($user['id_jadwal_shift']);
        if (!$jadwalShift) {
            log_message('error', 'Jadwal shift tidak ditemukan untuk ID: ' . $user['id_jadwal_shift']);
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Jadwal shift tidak ditemukan'
            ]);
        }

        // Debug untuk melihat data jadwal shift secara detail
        log_message('debug', 'Jadwal Shift Data Detail:');
        log_message('debug', 'ID: ' . $jadwalShift['id_jadwal_shift']);
        log_message('debug', 'Dari Tanggal: ' . $jadwalShift['dari_tanggal']);
        log_message('debug', 'Sampai Tanggal: ' . $jadwalShift['sampai_tanggal']);
        log_message('debug', 'Shift Mulai: ' . $jadwalShift['shift_mulai']);
        log_message('debug', 'Shift Selesai: ' . $jadwalShift['shift_selesai']);
        log_message('debug', 'Jenis Shift: ' . $jadwalShift['jenis_shift']);

        // Validasi data jadwal shift dengan pesan yang lebih spesifik
        if (empty($jadwalShift['shift_mulai'])) {
            log_message('error', 'Shift mulai kosong: ' . json_encode($jadwalShift));
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Jam mulai shift tidak ditemukan'
            ]);
        }

        if (empty($jadwalShift['shift_selesai'])) {
            log_message('error', 'Shift selesai kosong: ' . json_encode($jadwalShift));
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Jam selesai shift tidak ditemukan'
            ]);
        }

        // Cek apakah user sudah absen hari ini
        $today = date('Y-m-d');
        $existingPresensi = $this->presensiModel->where([
            'tb_user_iduser' => $user['iduser'],
            'tanggal' => $today
        ])->first();

        if ($existingPresensi) {
            // Update jam pulang
            $jamPulang = date('H:i:s');
            $this->presensiModel->update($existingPresensi['id_presensi'], [
                'jam_pulang' => $jamPulang,
                'diupdate_pada' => date('Y-m-d H:i:s')
            ]);
            
            // Hitung persentase kehadiran
            $persentase = $this->hitungPersentase(
                $jadwalShift['shift_mulai'],
                $jadwalShift['shift_selesai'],
                $existingPresensi['jam_masuk'],
                $jamPulang
            );

            // Update persentase
            $this->presensiModel->update($existingPresensi['id_presensi'], [
                'persentase' => $persentase
            ]);
            
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Absen pulang berhasil',
                'data' => [
                    'nama' => $user['nama'],
                    'jam_masuk' => $existingPresensi['jam_masuk'],
                    'jam_pulang' => $jamPulang,
                    'keterangan' => $existingPresensi['keterangan'],
                    'persentase' => $persentase
                ]
            ]);
        } else {
            $jamMasuk = date('H:i:s');
            $keterangan = 'hadir';
            
            // Cek keterlambatan
            if ($jamMasuk > $jadwalShift['shift_mulai']) {
                $keterangan = 'telat';
            }

            // Hitung persentase kehadiran
            $persentase = $this->hitungPersentase(
                $jadwalShift['shift_mulai'],
                $jadwalShift['shift_selesai'],
                $jamMasuk
            );

            // Insert presensi baru
            $this->presensiModel->insert([
                'tanggal' => $today,
                'jam_masuk' => $jamMasuk,
                'keterangan' => $keterangan,
                'persentase' => $persentase,
                'tb_user_iduser' => $user['iduser'],
                'tb_jadwal_shift_id_jadwal_shift' => $user['id_jadwal_shift'],
                'dibuat_pada' => date('Y-m-d H:i:s'),
                'diupdate_pada' => date('Y-m-d H:i:s')
            ]);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Absen masuk berhasil',
                'data' => [
                    'nama' => $user['nama'],
                    'jam_masuk' => $jamMasuk,
                    'keterangan' => $keterangan,
                    'persentase' => $persentase
                ]
            ]);
        }
    }

    private function hitungPersentase($shiftMulai, $shiftSelesai, $jamMasuk, $jamPulang = null)
    {
        // Debug untuk melihat parameter yang diterima
        log_message('debug', 'Parameter hitungPersentase: ' . json_encode([
            'shiftMulai' => $shiftMulai,
            'shiftSelesai' => $shiftSelesai,
            'jamMasuk' => $jamMasuk,
            'jamPulang' => $jamPulang
        ]));

        // Konversi waktu ke menit untuk perhitungan
        $shiftMulaiMenit = $this->waktuKeMenit($shiftMulai);
        $shiftSelesaiMenit = $this->waktuKeMenit($shiftSelesai);
        $jamMasukMenit = $this->waktuKeMenit($jamMasuk);

        // Hitung total durasi shift dalam menit
        $totalDurasiShift = $shiftSelesaiMenit - $shiftMulaiMenit;

        // Hitung keterlambatan masuk (dalam menit)
        $keterlambatanMasuk = max(0, $jamMasukMenit - $shiftMulaiMenit);

        // Jika sudah ada jam pulang, hitung juga keterlambatan pulang
        if ($jamPulang) {
            $jamPulangMenit = $this->waktuKeMenit($jamPulang);
            $keterlambatanPulang = max(0, $shiftSelesaiMenit - $jamPulangMenit);
        } else {
            $keterlambatanPulang = 0;
        }

        // Hitung total keterlambatan
        $totalKeterlambatan = $keterlambatanMasuk + $keterlambatanPulang;

        // Hitung persentase kehadiran
        // Rumus: ((Total Durasi Shift - Total Keterlambatan) / Total Durasi Shift) * 100
        $persentase = (($totalDurasiShift - $totalKeterlambatan) / $totalDurasiShift) * 100;

        // Batasi persentase antara 0 dan 100
        return max(0, min(100, round($persentase, 2)));
    }

    private function waktuKeMenit($waktu)
    {
        if (empty($waktu)) {
            log_message('error', 'Waktu kosong: ' . $waktu);
            return 0;
        }
        
        list($jam, $menit) = explode(':', $waktu);
        return ($jam * 60) + $menit;
    }

    public function getByMonth($bulan)
    {
        $presensi = $this->presensiModel->getPresensiWithUser()
            ->where('MONTH(tanggal)', $bulan)
            ->where('YEAR(tanggal)', date('Y'))
            ->findAll();

        return $this->response->setJSON($presensi);
    }

    public function scan()
    {
        return view('presensi/scan');
    }

    public function debugUser($rfid)
    {
        $user = $this->userModel->where('id_kartu_rfid', $rfid)->first();
        if (!$user) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User tidak ditemukan'
            ]);
        }

        $jadwalShift = null;
        if (!empty($user['id_jadwal_shift'])) {
            $jadwalShift = $this->jadwalShiftModel->find($user['id_jadwal_shift']);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => [
                'user' => $user,
                'jadwal_shift' => $jadwalShift
            ]
        ]);
    }

    public function checkUserData($rfid)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('tb_user');
        $builder->select('tb_user.*, tb_jadwal_shift.*');
        $builder->join('tb_jadwal_shift', 'tb_jadwal_shift.id_jadwal_shift = tb_user.id_jadwal_shift', 'left');
        $builder->where('tb_user.id_kartu_rfid', $rfid);
        $result = $builder->get()->getRowArray();

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $result
        ]);
    }
}
