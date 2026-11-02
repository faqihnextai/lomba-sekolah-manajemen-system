<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function settingDokumen()
    {
        return view('admin.setting-dokumen');
    }

    public function manajemenUser()
    {
        return view('admin.manajemen-user');
    }

    public function lombaAdiwiyata()
    {
        return view('admin.lomba-adiwiyata');
    }

    public function lombaKantinSehat()
    {
        return view('admin.lomba-kantin-sehat');
    }

    public function lombaSekolahSehat()
    {
        return view('admin.lomba-sekolah-sehat');
    }
}