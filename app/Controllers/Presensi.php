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

        // Cari user berdasarkan id_kartu_rfid
        $user = $this->userModel->where('id_kartu_rfid', $rfid)->first();

        if (!$user) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Kartu tidak terdaftar'
            ]);
        }

        // Cek apakah user memiliki jadwal shift
        if (!$user['id_jadwal_shift']) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User tidak memiliki jadwal shift'
            ]);
        }

        // Ambil jadwal shift user
        $jadwalShift = $this->jadwalShiftModel->find($user['id_jadwal_shift']);

        // Cek apakah hari ini adalah hari kerja
        $hariIni = date('N'); // 1 (Senin) sampai 7 (Minggu)
        if (!in_array($hariIni, explode(',', $jadwalShift['hari']))) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Hari ini bukan hari kerja Anda'
            ]);
        }

        // Cek apakah sudah ada presensi hari ini
        $today = date('Y-m-d');
        $existingPresensi = $this->presensiModel->where([
            'tb_user_iduser' => $user['iduser'],
            'tanggal' => $today
        ])->first();

        $currentTime = date('H:i:s');

        if ($existingPresensi) {
            // Update jam pulang
            $this->presensiModel->update($existingPresensi['id_presensi'], [
                'jam_pulang' => $currentTime,
                'persentase' => $this->hitungPersentase($jadwalShift['shift_mulai'], $jadwalShift['shift_selesai'], $existingPresensi['jam_masuk'], $currentTime)
            ]);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Jam pulang berhasil dicatat',
                'data' => [
                    'nama' => $user['nama'],
                    'jam_pulang' => $currentTime
                ]
            ]);
        } else {
            // Buat presensi baru
            $this->presensiModel->insert([
                'tanggal' => $today,
                'jam_masuk' => $currentTime,
                'tb_user_iduser' => $user['iduser'],
                'tb_jadwal_shift_id_jadwal_shift' => $user['id_jadwal_shift'],
                'persentase' => $this->hitungPersentase($jadwalShift['shift_mulai'], $jadwalShift['shift_selesai'], $currentTime, null)
            ]);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Jam masuk berhasil dicatat',
                'data' => [
                    'nama' => $user['nama'],
                    'jam_masuk' => $currentTime
                ]
            ]);
        }
    }

    private function hitungPersentase($shiftMulai, $shiftSelesai, $jamMasuk, $jamPulang = null)
    {
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
}
