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
        try {
            // Get current month if not specified
            $bulan = $this->request->getGet('bulan') ?? date('m');

            // Get data for selected month
            $data['presensi'] = $this->presensiModel->getPresensiWithUser()
                ->where('MONTH(tanggal)', $bulan)
                ->where('YEAR(tanggal)', date('Y'))
                ->findAll();

            // Set current month for view
            $data['currentMonth'] = $bulan;

            // Jika tidak ada data, set array kosong
            if (empty($data['presensi'])) {
                $data['presensi'] = [];
            }

            return view('presensi/index', $data);
        } catch (\Exception $e) {
            // Log error
            log_message('error', 'Error in Presensi::index: ' . $e->getMessage());

            // Set data kosong dan tampilkan pesan error
            $data['presensi'] = [];
            $data['error'] = 'Terjadi kesalahan saat mengambil data presensi';
            return view('presensi/index', $data);
        }
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

        // Pastikan format bulan adalah 2 digit
        $bulan = str_pad($bulan, 2, '0', STR_PAD_LEFT);

        // Get month name
        $monthNames = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember'
        ];
        $monthName = $monthNames[$bulan] ?? '';
        $tahun = date('Y');

        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="Laporan_Presensi_' . $monthName . '_' . $tahun . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Create output stream
        $output = fopen('php://output', 'w');

        // Add BOM for proper Excel encoding
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Add CSV headers
        fputcsv($output, ['LAPORAN PRESENSI KARYAWAN']);
        fputcsv($output, ['Periode: ' . $monthName . ' ' . $tahun]);
        fputcsv($output, ['']); // Empty line for spacing
        fputcsv($output, ['No', 'Tanggal', 'Nama Karyawan', 'Shift', 'Jam Masuk', 'Jam Pulang', 'Persentase', 'Status']);

        // Get data directly from database
        $db = \Config\Database::connect();
        $builder = $db->table('tb_presensi');
        $builder->select('tb_presensi.*, tb_user.nama, tb_jadwal_shift.jenis_shift');
        $builder->join('tb_user', 'tb_user.iduser = tb_presensi.tb_user_iduser');
        $builder->join('tb_jadwal_shift', 'tb_jadwal_shift.id_jadwal_shift = tb_presensi.tb_jadwal_shift_id_jadwal_shift');
        $builder->where('MONTH(tb_presensi.tanggal)', $bulan);
        $builder->where('YEAR(tb_presensi.tanggal)', $tahun);
        $builder->orderBy('tb_presensi.tanggal', 'ASC');
        $builder->orderBy('tb_user.nama', 'ASC');
        $query = $builder->get();
        $presensi = $query->getResultArray();

        // Add data rows
        $no = 1;
        foreach ($presensi as $row) {
            // Format status
            $status = '';
            if ($row['keterangan'] == 'telat') {
                $status = 'Terlambat';
            } elseif ($row['keterangan'] == 'hadir') {
                $status = 'Hadir';
            } else {
                $status = $row['keterangan'];
            }

            fputcsv($output, [
                $no++,
                date('d-m-Y', strtotime($row['tanggal'])),
                $row['nama'],
                $row['jenis_shift'],
                $row['jam_masuk'] ?? '-',
                $row['jam_pulang'] ?? '-',
                number_format($row['persentase'], 2) . '%',
                $status
            ]);
        }

        // Add summary
        fputcsv($output, ['']); // Empty line for spacing
        fputcsv($output, ['Total Karyawan: ' . count($presensi)]);
        fputcsv($output, ['Tanggal Export: ' . date('d-m-Y H:i:s')]);

        fclose($output);
        exit;
    }

    public function scanRfid()
    {
        // Terima data RFID dari request sebagai string
        $rfid = $this->request->getJSON()->rfid;

        // Debug untuk melihat RFID yang diterima
        log_message('debug', 'RFID yang diterima: ' . $rfid);

        // Cari user berdasarkan RFID (sebagai string)
        $user = $this->userModel->where('id_kartu_rfid', $rfid)->first();

        if (!$user) {
            log_message('error', 'User tidak ditemukan untuk RFID: ' . $rfid);
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Kartu RFID tidak terdaftar'
            ]);
        }

        // Debug untuk melihat data user
        log_message('debug', 'Data User: ' . json_encode($user));

        // Cek jadwal shift user
        if (!isset($user['id_jadwal_shift']) || empty($user['id_jadwal_shift'])) {
            log_message('error', 'User ' . $user['nama'] . ' belum memiliki jadwal shift');
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User belum memiliki jadwal shift'
            ]);
        }

        // Ambil jadwal shift
        $jadwalShift = $this->jadwalShiftModel->find($user['id_jadwal_shift']);
        if (!$jadwalShift) {
            log_message('error', 'Jadwal shift tidak ditemukan untuk ID: ' . $user['id_jadwal_shift']);
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Jadwal shift tidak ditemukan'
            ]);
        }

        // Debug untuk melihat data jadwal shift
        log_message('debug', 'Jadwal Shift Data: ' . json_encode($jadwalShift));

        // Cek apakah sudah ada presensi hari ini
        $today = date('Y-m-d');
        $existingPresensi = $this->presensiModel->where([
            'tanggal' => $today,
            'tb_user_iduser' => $user['iduser']
        ])->first();

        $currentTime = date('H:i:s');
        $data = [
            'tanggal' => $today,
            'tb_user_iduser' => $user['iduser'],
            'tb_jadwal_shift_id_jadwal_shift' => $user['id_jadwal_shift'],
            'dibuat_pada' => date('Y-m-d H:i:s'),
            'diupdate_pada' => date('Y-m-d H:i:s')
        ];

        if (!$existingPresensi) {
            // Presensi masuk
            $data['jam_masuk'] = $currentTime;

            // Cek keterlambatan
            if (strtotime($currentTime) > strtotime($jadwalShift['shift_mulai'])) {
                $data['keterangan'] = 'telat';
            } else {
                $data['keterangan'] = 'hadir';
            }

            // Insert presensi baru
            $this->presensiModel->insert($data);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Presensi masuk berhasil',
                'data' => [
                    'nama' => $user['nama'],
                    'jam_masuk' => $currentTime,
                    'keterangan' => $data['keterangan']
                ]
            ]);
        } else {
            // Presensi pulang
            $data['jam_pulang'] = $currentTime;

            // Update presensi yang ada
            $this->presensiModel->update($existingPresensi['id_presensi'], $data);

            // Hitung persentase kehadiran
            $persentase = $this->hitungPersentase(
                $jadwalShift['shift_mulai'],
                $jadwalShift['shift_selesai'],
                $existingPresensi['jam_masuk'],
                $currentTime
            );

            // Update persentase
            $this->presensiModel->update($existingPresensi['id_presensi'], ['persentase' => $persentase]);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Presensi pulang berhasil',
                'data' => [
                    'nama' => $user['nama'],
                    'jam_pulang' => $currentTime,
                    'persentase' => $persentase
                ]
            ]);
        }
    }

    private function hitungPersentase($shiftMulai, $shiftSelesai, $jamMasuk, $jamPulang = null)
    {
        // Jika belum ada jam pulang, hitung berdasarkan jam masuk saja
        if (!$jamPulang) {
            // Jika jam masuk sama dengan shift_mulai, maka 100%
            if ($jamMasuk === $shiftMulai) {
                return 100;
            }
            // Jika jam masuk lebih awal dari shift_mulai, tetap 100%
            if (strtotime($jamMasuk) < strtotime($shiftMulai)) {
                return 100;
            }
            // Jika jam masuk lebih lambat dari shift_mulai, hitung keterlambatan
            $keterlambatan = strtotime($jamMasuk) - strtotime($shiftMulai);
            $totalShift = strtotime($shiftSelesai) - strtotime($shiftMulai);
            $persentase = 100 - (($keterlambatan / $totalShift) * 100);
            return max(0, min(100, round($persentase, 2)));
        }

        // Jika sudah ada jam pulang, hitung berdasarkan jam masuk dan pulang
        $totalShift = strtotime($shiftSelesai) - strtotime($shiftMulai);
        $totalHadir = strtotime($jamPulang) - strtotime($jamMasuk);

        // Jika total hadir lebih dari total shift, tetap 100%
        if ($totalHadir >= $totalShift) {
            return 100;
        }

        // Hitung persentase berdasarkan total hadir dibagi total shift
        $persentase = ($totalHadir / $totalShift) * 100;
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

    public function scanRfidDevice()
    {
        // Terima data dari alat RFID
        $rfid = $this->request->getPost('rfid');

        // Debug untuk melihat RFID yang diterima
        log_message('debug', 'RFID dari alat: ' . $rfid);

        // Cari user berdasarkan RFID
        $user = $this->userModel->where('id_kartu_rfid', $rfid)->first();

        if (!$user) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Kartu RFID tidak terdaftar'
            ]);
        }

        // Cek jadwal shift user
        if (!isset($user['id_jadwal_shift']) || empty($user['id_jadwal_shift'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User belum memiliki jadwal shift'
            ]);
        }

        // Ambil jadwal shift
        $jadwalShift = $this->jadwalShiftModel->find($user['id_jadwal_shift']);
        if (!$jadwalShift) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Jadwal shift tidak ditemukan'
            ]);
        }

        // Cek apakah sudah ada presensi hari ini
        $today = date('Y-m-d');
        $existingPresensi = $this->presensiModel->where([
            'tanggal' => $today,
            'tb_user_iduser' => $user['iduser']
        ])->first();

        // Cek cooldown untuk RFID yang sama (5 detik)
        $lastScan = $this->presensiModel->where('tb_user_iduser', $user['iduser'])
            ->orderBy('dibuat_pada', 'DESC')
            ->first();

        if ($lastScan) {
            $lastScanTime = strtotime($lastScan['dibuat_pada']);
            $currentTime = time();
            $cooldownTime = 5; // 5 detik dalam detik

            if (($currentTime - $lastScanTime) < $cooldownTime) {
                $remainingTime = $cooldownTime - ($currentTime - $lastScanTime);
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Mohon tunggu ' . ceil($remainingTime) . ' detik lagi sebelum scan ulang'
                ]);
            }
        }

        $currentTime = date('H:i:s');
        $data = [
            'tanggal' => $today,
            'tb_user_iduser' => $user['iduser'],
            'tb_jadwal_shift_id_jadwal_shift' => $user['id_jadwal_shift'],
            'dibuat_pada' => date('Y-m-d H:i:s'),
            'diupdate_pada' => date('Y-m-d H:i:s')
        ];

        // Cek apakah waktu scan di luar jadwal shift
        $shiftMulaiTime = strtotime($jadwalShift['shift_mulai']);
        $shiftSelesaiTime = strtotime($jadwalShift['shift_selesai']);
        $currentTimeStamp = strtotime($currentTime);

        if ($currentTimeStamp < $shiftMulaiTime || $currentTimeStamp > $shiftSelesaiTime) {
            $data['keterangan'] = 'tidak valid (di luar jam kerja)';
        } else {
            // Cek keterlambatan (lebih dari 1 jam)
            $oneHourInSeconds = 3600;
            if (($currentTimeStamp - $shiftMulaiTime) > $oneHourInSeconds) {
                $data['keterangan'] = 'terlambat';
            } else {
                $data['keterangan'] = 'hadir';
            }
        }

        if (!$existingPresensi) {
            // Presensi masuk
            $data['jam_masuk'] = $currentTime;
            $this->presensiModel->insert($data);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Presensi masuk berhasil',
                'data' => [
                    'nama' => $user['nama'],
                    'jam_masuk' => $currentTime,
                    'keterangan' => $data['keterangan']
                ],
                'refresh' => true
            ]);
        } else {
            // Presensi pulang
            $data['jam_pulang'] = $currentTime;

            // Update presensi yang ada
            $this->presensiModel->update($existingPresensi['id_presensi'], $data);

            // Hitung persentase kehadiran
            $persentase = $this->hitungPersentase(
                $jadwalShift['shift_mulai'],
                $jadwalShift['shift_selesai'],
                $existingPresensi['jam_masuk'],
                $currentTime
            );

            // Update persentase
            $this->presensiModel->update($existingPresensi['id_presensi'], ['persentase' => $persentase]);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Presensi pulang berhasil',
                'data' => [
                    'nama' => $user['nama'],
                    'jam_pulang' => $currentTime,
                    'persentase' => $persentase
                ],
                'refresh' => true
            ]);
        }
    }

    public function publicScan()
    {
        return view('presensi/public_scan');
    }

    public function publicScanRfid()
    {
        // Terima data RFID dari request sebagai string
        $rfid = $this->request->getJSON()->rfid;

        // Debug untuk melihat RFID yang diterima
        log_message('debug', 'RFID yang diterima: ' . $rfid);

        // Cari user berdasarkan RFID (sebagai string)
        $user = $this->userModel->where('id_kartu_rfid', $rfid)->first();

        if (!$user) {
            log_message('error', 'User tidak ditemukan untuk RFID: ' . $rfid);
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Kartu RFID tidak terdaftar'
            ]);
        }

        // Cek jadwal shift user
        if (!isset($user['id_jadwal_shift']) || empty($user['id_jadwal_shift'])) {
            log_message('error', 'User ' . $user['nama'] . ' belum memiliki jadwal shift');
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User belum memiliki jadwal shift'
            ]);
        }

        // Ambil jadwal shift
        $jadwalShift = $this->jadwalShiftModel->find($user['id_jadwal_shift']);
        if (!$jadwalShift) {
            log_message('error', 'Jadwal shift tidak ditemukan untuk ID: ' . $user['id_jadwal_shift']);
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Jadwal shift tidak ditemukan'
            ]);
        }

        // Cek cooldown untuk RFID yang sama (5 detik)
        $lastScan = $this->presensiModel->where('tb_user_iduser', $user['iduser'])
            ->orderBy('dibuat_pada', 'DESC')
            ->first();

        if ($lastScan) {
            $lastScanTime = strtotime($lastScan['dibuat_pada']);
            $currentTime = time();
            $cooldownTime = 5; // 5 detik dalam detik

            if (($currentTime - $lastScanTime) < $cooldownTime) {
                $remainingTime = $cooldownTime - ($currentTime - $lastScanTime);
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Mohon tunggu ' . ceil($remainingTime) . ' detik lagi sebelum scan ulang',
                    'cooldown' => true
                ]);
            }
        }

        // Cek apakah sudah ada presensi hari ini
        $today = date('Y-m-d');
        $existingPresensi = $this->presensiModel->where([
            'tanggal' => $today,
            'tb_user_iduser' => $user['iduser']
        ])->first();

        $currentTime = date('H:i:s');
        $data = [
            'tanggal' => $today,
            'tb_user_iduser' => $user['iduser'],
            'tb_jadwal_shift_id_jadwal_shift' => $user['id_jadwal_shift'],
            'dibuat_pada' => date('Y-m-d H:i:s'),
            'diupdate_pada' => date('Y-m-d H:i:s')
        ];

        // Cek apakah waktu scan di luar jadwal shift
        $shiftMulaiTime = strtotime($jadwalShift['shift_mulai']);
        $shiftSelesaiTime = strtotime($jadwalShift['shift_selesai']);
        $currentTimeStamp = strtotime($currentTime);

        if ($currentTimeStamp < $shiftMulaiTime || $currentTimeStamp > $shiftSelesaiTime) {
            $data['keterangan'] = 'tidak valid (di luar jam kerja)';
        } else {
            // Cek keterlambatan (lebih dari 1 jam)
            $oneHourInSeconds = 3600;
            if (($currentTimeStamp - $shiftMulaiTime) > $oneHourInSeconds) {
                $data['keterangan'] = 'terlambat';
            } else {
                $data['keterangan'] = 'hadir';
            }
        }

        if (!$existingPresensi) {
            // Presensi masuk
            $data['jam_masuk'] = $currentTime;
            $this->presensiModel->insert($data);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Presensi masuk berhasil',
                'data' => [
                    'nama' => $user['nama'],
                    'jam_masuk' => $currentTime,
                    'keterangan' => $data['keterangan']
                ]
            ]);
        } else {
            // Presensi pulang
            $data['jam_pulang'] = $currentTime;

            // Update presensi yang ada
            $this->presensiModel->update($existingPresensi['id_presensi'], $data);

            // Hitung persentase kehadiran
            $persentase = $this->hitungPersentase(
                $jadwalShift['shift_mulai'],
                $jadwalShift['shift_selesai'],
                $existingPresensi['jam_masuk'],
                $currentTime
            );

            // Update persentase
            $this->presensiModel->update($existingPresensi['id_presensi'], ['persentase' => $persentase]);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Presensi pulang berhasil',
                'data' => [
                    'nama' => $user['nama'],
                    'jam_pulang' => $currentTime,
                    'persentase' => $persentase
                ]
            ]);
        }
    }
}
