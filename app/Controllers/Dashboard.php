<?php

namespace App\Controllers;

use App\Models\PresensiModel;
use App\Models\UserModel;

class Dashboard extends BaseController
{
    protected $presensiModel;
    protected $userModel;

    public function __construct()
    {
        $this->presensiModel = new PresensiModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $data['total_users'] = $this->userModel->countAll();
        $data['total_presensi'] = $this->presensiModel->countAll();
        $data['presensi_hari_ini'] = $this->presensiModel->where('tanggal', date('Y-m-d'))->countAllResults();

        return view('dashboard/index', $data);
    }
}
